<?php
// Real controller queries against an isolated SQLite database; no production data.
$root = dirname(__DIR__);
$loader = require ($argv[1] ?? $root.'/vendor/autoload.php');
$loader->setPsr4('App\\', $root.'/app');
// Override any optimized Composer classmap from a different checkout.
foreach ($loader->getClassMap() as $class => $path) {
    if (str_starts_with($class, 'App\\')) {
        $local = $root.'/app/'.str_replace('\\', '/', substr($class, 4)).'.php';
        if (is_file($local)) $loader->addClassMap([$class => $local]);
    }
}

use Illuminate\Config\Repository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Admin\AdminAtsOverviewController;
use App\Http\Controllers\Admin\AdminBaseController;
use App\Http\Controllers\Admin\AdminJobApplicationController;

$app = new Application($root);
$app->instance('config', new Repository([
    'app' => ['timezone' => 'UTC', 'url' => 'https://ats.example.test'],
    'database' => ['default' => 'sqlite', 'connections' => ['sqlite' => [
        'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
    ]]],
    'view' => ['paths' => [$root.'/resources/views'], 'compiled' => sys_get_temp_dir()],
]));
Facade::setFacadeApplication($app);
foreach ([Illuminate\Events\EventServiceProvider::class, Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    Illuminate\Routing\RoutingServiceProvider::class, Illuminate\View\ViewServiceProvider::class,
    Yajra\DataTables\DataTablesServiceProvider::class] as $provider) {
    $app->register($provider);
}
$app->boot();

Schema::create('job_applications', function (Blueprint $t) {
    $t->increments('id'); $t->unsignedInteger('job_id'); $t->unsignedInteger('status_id');
    $t->string('full_name'); $t->string('email')->nullable(); $t->boolean('is_candidate')->default(false);
    $t->unsignedInteger('location_id')->nullable(); $t->text('skills')->nullable(); $t->softDeletes(); $t->timestamps();
});
Schema::create('consortium_registration_job_moves', function (Blueprint $t) {
    $t->increments('id'); $t->unsignedInteger('job_application_id');
});
Schema::create('jobs', function (Blueprint $t) {
    $t->increments('id'); $t->string('title'); $t->unsignedInteger('company_id'); $t->unsignedInteger('location_id');
    $t->date('start_date'); $t->date('end_date'); $t->string('status');
});
Schema::create('companies', function (Blueprint $t) { $t->increments('id'); $t->string('company_name'); });
Schema::create('job_locations', function (Blueprint $t) { $t->increments('id'); $t->string('location'); });
Schema::create('job_job_locations', function (Blueprint $t) { $t->increments('id'); $t->unsignedInteger('job_id'); $t->unsignedInteger('location_id'); });
Schema::create('application_status', function (Blueprint $t) {
    $t->increments('id'); $t->unsignedInteger('job_id'); $t->string('status'); $t->string('color'); $t->integer('position');
});
Schema::create('job_skills', function (Blueprint $t) { $t->increments('id'); $t->unsignedInteger('job_id'); });
Schema::create('questions', function (Blueprint $t) {
    $t->increments('id'); $t->string('type'); $t->boolean('is_knockout'); $t->string('knockout_answer')->nullable();
});
Schema::create('job_application_answers', function (Blueprint $t) {
    $t->increments('id'); $t->unsignedInteger('job_application_id'); $t->unsignedInteger('question_id'); $t->text('answer');
});
Schema::create('documents', function (Blueprint $t) {
    $t->increments('id'); $t->unsignedInteger('documentable_id'); $t->string('documentable_type'); $t->string('name');
});
DB::table('companies')->insert(['id' => 1, 'company_name' => 'Graybar Canada']);
DB::table('job_locations')->insert(['id' => 1, 'location' => 'Oshawa']);
foreach ([1, 2] as $job) {
    DB::table('jobs')->insert(['id' => $job, 'title' => 'Job '.$job, 'company_id' => 1, 'location_id' => 1,
        'start_date' => '2000-01-01', 'end_date' => '2099-01-01', 'status' => 'active']);
    foreach ([1 => 'Applied', 2 => 'Rejected'] as $position => $status) {
        DB::table('application_status')->insert(['id' => ($job - 1) * 2 + $position, 'job_id' => $job,
            'status' => $status, 'position' => $position, 'color' => '#2563EB']);
    }
}
function candidate(int $id, string|null $email, int $job = 1, bool $deleted = false, bool $internal = false): void {
    DB::table('job_applications')->insert(['id' => $id, 'job_id' => $job, 'status_id' => $job === 1 ? 1 : 3,
        'full_name' => 'Candidate '.$id, 'email' => $email, 'is_candidate' => $internal,
        'location_id' => 1, 'deleted_at' => $deleted ? '2026-10-01 00:00:00' : null,
        'created_at' => '2026-09-01 00:00:00']);
}
for ($id = 1; $id <= 6; $id++) candidate($id, 'candidate'.$id.'@example.test');
candidate(7, 'candidate5@example.test', deleted: true);
candidate(8, 'candidate6@example.test', deleted: true);
candidate(9, 'candidate1@example.test', internal: true);
DB::table('consortium_registration_job_moves')->insert(['job_application_id' => 7]);
DB::table('job_applications')->where('id', 6)->update(['status_id' => 2]);
DB::table('questions')->insert(['id' => 1, 'type' => 'radio', 'is_knockout' => true, 'knockout_answer' => 'No']);
DB::table('job_application_answers')->insert(['job_application_id' => 5, 'question_id' => 1, 'answer' => 'No']);

function check(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
function controller(string $class): object {
    $controller = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    $actor = new class { public $role_id = 2; public function cans($permission) { return $permission === 'view_job_applications'; } };
    (new ReflectionProperty(AdminBaseController::class, 'user'))->setValue($controller, $actor);
    return $controller;
}
function verify(int $expected, string $case): void {
    global $app;
    $request = Request::create('https://ats.example.test/admin/job-applications/data', 'GET', [
        'jobs' => '1', 'company' => 'all', 'location' => 'all', 'status' => 'all', 'questions' => 'all',
        'draw' => 1, 'start' => 0, 'length' => 100,
    ]);
    $app->instance('request', $request);
    $applications = controller(AdminJobApplicationController::class);
    $overview = controller(AdminAtsOverviewController::class)->index()->getData()['jobs']->firstWhere('id', 1);
    $table = $applications->data($request)->getData(true);
    check(!isset($table['error']), $table['error'] ?? 'DataTables failed');
    $counts = $applications->stageCounts($request);
    check($overview->applicant_count === $expected, "$case: overview count mismatch");
    check($table['recordsFiltered'] === $expected && count($table['data']) === $expected, "$case: Job Applications hides applicants");
    check((int) $counts['counts']->sum() === $expected, "$case: All Applicants badge mismatch");
    $overviewIds = $overview->statuses->flatMap(fn ($status) => $status->applicants->pluck('id'))->sort()->values()->all();
    $tableIds = array_column($table['data'], 'id'); sort($tableIds);
    check($tableIds === $overviewIds, "$case: the pages show different applicants");
    foreach ($overview->statuses as $status) {
        check((int) ($counts['counts'][$status->id] ?? 0) === $status->applicant_count, "$case: stage badges disagree with overview");
    }
    check((int) $counts['ko_count'] === 1, "$case: knockout badge must retain the restored active applicant");
    // The shared OR clause must not bypass company, location, job or stage filters.
    $request->query->add(['company' => '1', 'location' => '1', 'status' => '2']);
    $rejected = $applications->data($request)->getData(true);
    check($rejected['recordsFiltered'] === 1 && array_column($rejected['data'], 'id') === [6], "$case: stage filtering includes unrelated applicants");
    echo "PASS: $case ($expected applicants on both pages and in stage counts)\n";
}
verify(6, 'Newer deleted duplicates do not hide active applicants');
candidate(10, 'candidate2@example.test');
verify(6, 'Live duplicates remain merged');
candidate(11, 'candidate2@example.test', job: 2);
verify(5, 'Ordinary duplicates still use the newest live application across jobs');
DB::table('consortium_registration_job_moves')->insert(['job_application_id' => 10]);
verify(6, 'Explicit Consortium assignments remain visible on both pages');
candidate(12, null);
verify(7, 'Missing email uses the same visibility rule on both pages');
echo "Applicant count integration checks passed.\n";
