<?php
// Run: php tests/candidate-communications-smoke.php
// Uses SQLite in memory and fake transports; never connects to the application database or providers.
require __DIR__.'/../vendor/autoload.php';

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

$app = new Application(dirname(__DIR__));
$app->instance('config', new Repository());
Facade::setFacadeApplication($app);
$db = new Capsule($app);
$db->addConnection(['driver'=>'sqlite','database'=>':memory:','prefix'=>'']);
$db->setAsGlobal();
$db->bootEloquent();
$app->instance('db', $db->getDatabaseManager());
$app->bind('db.schema', fn () => $db->schema());
$views = Mockery::mock(\Illuminate\Contracts\View\Factory::class);
$app->instance(\Illuminate\Contracts\Routing\ResponseFactory::class, new ResponseFactory($views, Mockery::mock(\Illuminate\Routing\Redirector::class)));
$validator = new \Illuminate\Validation\Factory(new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader(), 'en'));
Request::macro('validate', function (array $rules) use ($validator) { return $validator->make($this->all(), $rules)->validate(); });
\Illuminate\Support\Facades\Log::swap(Mockery::mock(\Psr\Log\LoggerInterface::class)->shouldIgnoreMissing());
$transport = new ArrayTransport();
$mailer = new Mailer('test', $views, $transport);
$mailer->alwaysFrom('ats@example.test', 'ATS');
foreach (['job_applications','consortium_registrations'] as $table) {
    $db->schema()->create($table, function (Blueprint $table) {
        $table->id();
        foreach (['full_name','first_name','last_name','email','phone'] as $column) $table->string($column)->nullable();
        $table->boolean('sms_consent')->default(false);
        $table->timestamp('moved_to_trash_at')->nullable();
        $table->softDeletes();
    });
}
(require __DIR__.'/../database/migrations/2026_09_25_000001_create_candidate_email_templates_table.php')->up();
class SmokeCommunicationController extends CandidateCommunicationController {
    public function __construct(private Mailer $testMailer, int $id = 1, bool $allowed = true) {
        $this->user = new class($id, $allowed) {
            public function __construct(public int $id, private bool $allowed) {}
            public function cans($permission) { return $this->allowed; }
        };
    }
    protected function mailer(string $source) { return $this->testMailer; }
}
function req(array $data): Request { return Request::create('/', 'POST', $data); }
function check(bool $value, string $message): void { if (!$value) throw new RuntimeException($message); echo "PASS: ".$message.PHP_EOL; }
function rejects(callable $call, string $class, string $message): void {
    try { $call(); } catch (Throwable $e) { check($e instanceof $class, $message); return; }
    throw new RuntimeException($message);
}
$c = new SmokeCommunicationController($mailer);
$db->table('job_applications')->insert([
    ['id'=>1,'full_name'=>'<Alice>','email'=>'a@example.test','deleted_at'=>'2026-01-01','moved_to_trash_at'=>null],
    ['id'=>2,'full_name'=>'Bob','email'=>'b@example.test','deleted_at'=>null,'moved_to_trash_at'=>null],
    ['id'=>3,'full_name'=>'Duplicate','email'=>'A@example.test','deleted_at'=>null,'moved_to_trash_at'=>null],
    ['id'=>4,'full_name'=>'Trash','email'=>'trash@example.test','deleted_at'=>null,'moved_to_trash_at'=>'2026-01-01'],
]);
$recipients = array_map(fn ($id) => ['type'=>'application','id'=>$id], [1,2,3,4]);
$preview = $c->preview(req(['channel'=>'email','recipients'=>$recipients]), new TelnyxSmsService())->getData(true)['recipients'];
check($preview[0]['reason'] === null && $preview[2]['reason'] === 'Duplicate contact.' && $preview[3]['reason'] === 'Candidate is no longer available.', 'Archived candidates allowed; duplicate contacts and trash excluded');
$result = $c->send(req(['channel'=>'email','subject'=>'Hello [applicant_name]','message'=>'Hi [applicant_name] <script>','recipients'=>$recipients]), new TelnyxSmsService())->getData(true)['results'];
check(array_column($result,'status') === ['sent','sent','skipped','skipped'], 'Bulk email reports sent and skipped recipients');
$messages = $transport->messages();
check(count($messages) === 2 && count($messages[0]->getOriginalMessage()->getTo()) === 1, 'Separate email per unique recipient');
check($messages[0]->getOriginalMessage()->getSubject() === 'Hello <Alice>' && str_contains($messages[0]->getOriginalMessage()->getHtmlBody(), '&lt;Alice&gt; &lt;script&gt;'), 'Personalization and HTML escaping');
$db->table('consortium_registrations')->insert([
    ['id'=>1,'first_name'=>'No consent','phone'=>'4165550101','sms_consent'=>false],
    ['id'=>2,'first_name'=>'Missing phone','phone'=>null,'sms_consent'=>true],
    ['id'=>3,'first_name'=>'Provider failure','phone'=>'4165550103','sms_consent'=>true],
]);
$sms = new class extends TelnyxSmsService {
    public int $calls = 0;
    public function send(string $phone, string $message): string { $this->calls++; throw new RuntimeException('Test provider failure'); }
};
$result = $c->send(req(['channel'=>'sms','message'=>'Hi [applicant_name]','recipients'=>array_map(fn($id)=>['type'=>'registration','id'=>$id],[1,2,3])]), $sms)->getData(true)['results'];
check(array_column($result,'status') === ['skipped','skipped','failed'] && $sms->calls === 1, 'SMS consent, missing contact, and provider failure handling');
$template = ['name'=>'Interview','subject'=>'Invite','message'=>'Hi [applicant_name]'];
$c->saveTemplate(req($template));
$c->saveTemplate(req(array_replace($template,['subject'=>'Updated'])));
$other = new SmokeCommunicationController($mailer, 2);
$other->saveTemplate(req($template));
$mine = $c->templates()->getData(true)['templates'];
$theirs = $other->templates()->getData(true)['templates'];
check(count($mine) === 1 && $mine[0]['subject'] === 'Updated' && $theirs[0]['subject'] === 'Invite' && $mine[0]['id'] !== $theirs[0]['id'], 'Template update, reuse, and owner isolation');
rejects(fn()=>(new SmokeCommunicationController($mailer,1,false))->templates(), \Symfony\Component\HttpKernel\Exception\HttpException::class, 'Unauthorized template access rejected');
rejects(fn()=>(new SmokeCommunicationController($mailer,1,false))->send(req([]), $sms), \Symfony\Component\HttpKernel\Exception\HttpException::class, 'Unauthorized send rejected');
rejects(fn()=>$c->send(req(['channel'=>'sms','message'=>'Hello','recipients'=>[]]),$sms), \Illuminate\Validation\ValidationException::class, 'Empty selection rejected');
rejects(fn()=>$c->send(req(['channel'=>'sms','message'=>str_repeat('x',1601),'recipients'=>[['type'=>'application','id'=>1]]]),$sms), \Illuminate\Validation\ValidationException::class, 'Overlong SMS rejected');

// Exercise the real mailer selection, without SMTP connections or a settings-table fallback.
$sharedSmtp = [
    'transport'=>'smtp', 'host'=>'smtp.example.test', 'port'=>465, 'encryption'=>'ssl',
    'username'=>'test-account', 'password'=>'test-password',
    'from'=>['address'=>'sender@example.test','name'=>'Recruitment'],
];
$app['config']->set('mail.ai_search_smtp', $sharedSmtp);
$builtMailer = Mockery::mock(Mailer::class);
$builtMailer->shouldReceive('alwaysFrom')->twice()->with('sender@example.test', 'Recruitment');
$mailManager = Mockery::mock(Illuminate\Mail\MailManager::class);
$mailManager->shouldReceive('build')->twice()->with($sharedSmtp)->andReturn($builtMailer);
Illuminate\Support\Facades\Mail::swap($mailManager);
$actualController = new class extends CandidateCommunicationController {
    public function __construct() {}
    public function resolveMailer(string $source) { return $this->mailer($source); }
};
check($actualController->resolveMailer('ai-search') === $builtMailer && $actualController->resolveMailer('candidates') === $builtMailer, 'AI Search, profile, and bulk email share the same SMTP settings and sender');
$app['config']->set('mail.ai_search_smtp.password', '');
rejects(fn()=>$actualController->resolveMailer('candidates'), RuntimeException::class, 'Incomplete shared SMTP fails explicitly without switching accounts');

Mockery::close();
echo "All messaging smoke checks passed.".PHP_EOL;
