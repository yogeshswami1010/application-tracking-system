<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\CandidateCommunicationController;
use App\Services\TelnyxSmsService;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\TestCase;

class CandidateCommunicationTest extends TestCase
{
    private Capsule $db;
    private ArrayTransport $transport;
    private Mailer $mailer;

    protected function setUp(): void
    {
        parent::setUp();
        $app = new Application(dirname(__DIR__, 2));
        $app->instance('config', new Repository());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($app);
        $this->db = new Capsule($app);
        $this->db->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $this->db->setAsGlobal();
        $this->db->bootEloquent();
        $app->instance('db', $this->db->getDatabaseManager());
        $app->bind('db.schema', fn () => $this->db->schema());
        $views = $this->createMock(\Illuminate\Contracts\View\Factory::class);
        $app->instance(\Illuminate\Contracts\Routing\ResponseFactory::class, new ResponseFactory(
            $views, $this->createMock(\Illuminate\Routing\Redirector::class)
        ));
        $validator = new Factory(new Translator(new ArrayLoader(), 'en'));
        Request::macro('validate', function (array $rules) use ($validator) {
            return $validator->make($this->all(), $rules)->validate();
        });
        Log::swap(\Mockery::mock(\Psr\Log\LoggerInterface::class)->shouldIgnoreMissing());
        $this->transport = new ArrayTransport();
        $this->mailer = new Mailer('test', $views, $this->transport);
        $this->mailer->alwaysFrom('ats@example.test', 'ATS');
        foreach (['job_applications', 'consortium_registrations'] as $table) {
            $this->db->schema()->create($table, function (Blueprint $table) {
                $table->id();
                $table->string('full_name')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->boolean('sms_consent')->default(false);
                $table->timestamp('moved_to_trash_at')->nullable();
                $table->softDeletes();
            });
        }
        (require dirname(__DIR__, 2).'/database/migrations/2026_09_25_000001_create_candidate_email_templates_table.php')->up();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        $this->db->getConnection()->disconnect();
        Request::flushMacros();
        Facade::clearResolvedInstances();
        parent::tearDown();
    }

    private function controller(int $userId = 1, bool $allowed = true): CandidateCommunicationController
    {
        return new class($this->mailer, $userId, $allowed) extends CandidateCommunicationController {
            public function __construct(private Mailer $testMailer, int $id, bool $allowed) {
                $this->user = new class($id, $allowed) {
                    public function __construct(public int $id, private bool $allowed) {}
                    public function cans($permission) { return $this->allowed; }
                };
            }
            protected function mailer(string $source) { return $this->testMailer; }
        };
    }

    private function request(array $data): Request { return Request::create('/', 'POST', $data); }

    public function test_preview_deduplicates_contacts_and_excludes_trash_but_includes_archived_candidates(): void
    {
        $this->db->table('job_applications')->insert([
            ['id'=>1, 'full_name'=>'Archived', 'email'=>'Person@example.test', 'deleted_at'=>'2026-01-01', 'moved_to_trash_at'=>null],
            ['id'=>2, 'full_name'=>'Trash', 'email'=>'trash@example.test', 'deleted_at'=>null, 'moved_to_trash_at'=>'2026-01-01'],
        ]);
        $this->db->table('consortium_registrations')->insert(['id'=>1, 'first_name'=>'Duplicate', 'email'=>'person@example.test']);
        $result = $this->controller()->preview($this->request(['channel'=>'email','recipients'=>[
            ['type'=>'application','id'=>1], ['type'=>'registration','id'=>1], ['type'=>'application','id'=>2]
        ]]), new TelnyxSmsService())->getData(true)['recipients'];
        $this->assertNull($result[0]['reason']);
        $this->assertSame('Duplicate contact.', $result[1]['reason']);
        $this->assertSame('Candidate is no longer available.', $result[2]['reason']);
    }

    public function test_email_is_personalized_escaped_and_sent_separately_with_duplicates_skipped(): void
    {
        $this->db->table('job_applications')->insert([
            ['id'=>1,'full_name'=>'<Alice>','email'=>'a@example.test'],
            ['id'=>2,'full_name'=>'Bob','email'=>'b@example.test'],
            ['id'=>3,'full_name'=>'Duplicate','email'=>'A@example.test'],
        ]);
        $result = $this->controller()->send($this->request([
            'channel'=>'email','subject'=>'Hello [applicant_name]','message'=>'Hi [applicant_name] <script>',
            'recipients'=>[['type'=>'application','id'=>1],['type'=>'application','id'=>2],['type'=>'application','id'=>3]],
        ]), new TelnyxSmsService())->getData(true);
        $this->assertSame(['sent','sent','skipped'], array_column($result['results'], 'status'));
        $messages = $this->transport->messages();
        $this->assertCount(2, $messages);
        $email = $messages[0]->getOriginalMessage();
        $this->assertCount(1, $email->getTo());
        $this->assertSame('Hello <Alice>', $email->getSubject());
        $this->assertStringContainsString('&lt;Alice&gt; &lt;script&gt;', $email->getHtmlBody());
    }

    public function test_sms_skips_nonconsenting_and_missing_contacts_and_reports_provider_failures(): void
    {
        $this->db->table('consortium_registrations')->insert([
            ['id'=>1,'first_name'=>'No consent','phone'=>'4165550101','sms_consent'=>false],
            ['id'=>2,'first_name'=>'No phone','phone'=>null,'sms_consent'=>true],
            ['id'=>3,'first_name'=>'Failure','phone'=>'4165550103','sms_consent'=>true],
        ]);
        $sms = $this->getMockBuilder(TelnyxSmsService::class)->onlyMethods(['send'])->getMock();
        $sms->expects($this->once())->method('send')->with('+14165550103', 'Hi Failure')->willThrowException(new \RuntimeException('Provider failed'));
        $result = $this->controller()->send($this->request([
            'channel'=>'sms','message'=>'Hi [applicant_name]',
            'recipients'=>[['type'=>'registration','id'=>1],['type'=>'registration','id'=>2],['type'=>'registration','id'=>3]],
        ]), $sms)->getData(true);
        $this->assertSame(['skipped','skipped','failed'], array_column($result['results'],'status'));
    }

    public function test_templates_are_reusable_updated_by_name_and_private_to_their_owner(): void
    {
        $template = ['name'=>'Interview','subject'=>'Invite','message'=>'Hi [applicant_name]'];
        $this->controller()->saveTemplate($this->request($template));
        $this->controller()->saveTemplate($this->request(array_replace($template,['subject'=>'Updated'])));
        $this->controller(2)->saveTemplate($this->request($template));
        $first = $this->controller()->templates()->getData(true)['templates'];
        $second = $this->controller(2)->templates()->getData(true)['templates'];
        $this->assertCount(1, $first);
        $this->assertCount(1, $second);
        $this->assertSame('Updated',$first[0]['subject']);
        $this->assertSame('Invite',$second[0]['subject']);
        $this->assertNotSame($first[0]['id'],$second[0]['id']);
    }

    public function test_permission_is_required_before_sending_or_reading_templates(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionCode(0);
        $this->controller(1, false)->templates();
    }

    public function test_empty_selection_is_rejected(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller()->send($this->request(['channel'=>'email','subject'=>'Test','message'=>'Test','recipients'=>[]]), new TelnyxSmsService());
    }

    public function test_overlong_sms_is_rejected(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller()->send($this->request(['channel'=>'sms','message'=>str_repeat('x',1601),'recipients'=>[['type'=>'application','id'=>1]]]), new TelnyxSmsService());
    }
}
