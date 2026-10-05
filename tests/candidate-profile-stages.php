<?php
// Run the actual controller method against an in-memory pipeline without Composer.
namespace ProfileStageTest {
    class Values {
        public function __construct(private array $rows) {}
        public function contains(callable $test) { foreach ($this->rows as $row) if ($test((object) $row)) return true; return false; }
        public function max($key) { return $this->rows ? max(array_column($this->rows, $key)) : null; }
    }
    class Query {
        public function __construct(private string $model, private ?int $id = null, private ?int $jobId = null) {}
        public function lockForUpdate() { $GLOBALS['stageLocks'][] = $this->model; return $this; }
        public function findOrFail($id) { foreach ($this->model::$rows as $row) if ($row['id'] === $id) return (object) $row; throw new \RuntimeException('Not found', 404); }
        public function firstOrFail() { return $this->findOrFail($this->id); }
        public function get() { return new Values(array_values(array_filter($this->model::$rows, fn ($row) => $row['job_id'] === $this->jobId))); }
    }
}
namespace Illuminate\Http {
    class Request {
        public function __construct(private array $data) {}
        public function input($key) { return $this->data[$key] ?? null; }
        public function merge($data) { $this->data = array_merge($this->data, $data); }
        public function validate($rules) {
            $GLOBALS['stageValidationRules'] = $rules;
            return array_intersect_key($this->data, $rules);
        }
    }
}
namespace Illuminate\Support\Facades {
    class DB {
        public static function transaction($fn) {
            $before = \App\ApplicationStatus::$rows;
            try { return $fn(); } catch (\Throwable $e) { \App\ApplicationStatus::$rows = $before; throw $e; }
        }
    }
}
namespace Illuminate\Validation {
    class ValidationException extends \RuntimeException {
        public static function withMessages($messages) { return new static(json_encode($messages)); }
    }
}
namespace App\Helper {
    class Reply { public static function successWithData($message, $data) { return ['status' => 'success', 'message' => $message] + $data; } }
}
namespace App\Traits { trait ZoomSettings {} }
namespace App {
    class JobApplication {
        public static array $rows = [];
        public static function withTrashed() { return new \ProfileStageTest\Query(static::class); }
    }
    class Job {
        public static array $rows = [];
        public static function whereKey($id) { return new \ProfileStageTest\Query(static::class, (int) $id); }
    }
    class ApplicationStatus {
        public static array $rows = [];
        public static function where($key, $jobId) { return new \ProfileStageTest\Query(static::class, null, $jobId); }
        public static function create($data) { $data['id'] = count(self::$rows) + 100; self::$rows[] = $data; return (object) $data; }
    }
}
namespace App\Http\Controllers\Admin {
    class AdminBaseController {
        protected $user;
        public function allow(array $permissions) {
            $this->user = new class($permissions) {
                public function __construct(private array $permissions) {}
                public function cans($permission) { return in_array($permission, $this->permissions, true); }
            };
        }
    }
    function abort_if($condition, $code) { if ($condition) throw new \RuntimeException('Forbidden', $code); }
}
namespace {
    require __DIR__.'/../app/Http/Controllers/Admin/AdminJobApplicationController.php';
    function check($condition, $message) { if (!$condition) throw new \RuntimeException($message); }
    function reject(callable $action, string $message) {
        try { $action(); } catch (\Throwable $e) { check(str_contains($e->getMessage(), $message), 'Unexpected failure: '.$e->getMessage()); return; }
        throw new \RuntimeException('Expected rejection: '.$message);
    }
    $controller = (new \ReflectionClass(\App\Http\Controllers\Admin\AdminJobApplicationController::class))->newInstanceWithoutConstructor();
    $controller->allow(['edit_job_applications', 'edit_jobs']);
    \App\Job::$rows = [['id' => 1], ['id' => 2]];
    \App\JobApplication::$rows = [
        ['id' => 3280, 'job_id' => 1, 'status_id' => 8, 'deleted_at' => null],
        ['id' => 3281, 'job_id' => 2, 'status_id' => 9, 'deleted_at' => '2026-10-05'],
        ['id' => 3282, 'job_id' => null, 'status_id' => 10, 'deleted_at' => null],
    ];
    \App\ApplicationStatus::$rows = [
        ['id' => 8, 'job_id' => 1, 'status' => 'Applied', 'position' => 1],
        ['id' => 11, 'job_id' => 1, 'status' => 'Interview', 'position' => 7],
        ['id' => 9, 'job_id' => 2, 'status' => 'Applied', 'position' => 20],
        ['id' => 10, 'job_id' => null, 'status' => 'Global', 'position' => 100],
    ];
    $candidatesBefore = \App\JobApplication::$rows;
    $oldStages = \App\ApplicationStatus::$rows;
    $request = fn ($name = ' Reference   check ') => new \Illuminate\Http\Request(['status_name' => $name, 'status_color' => '#7C3AED', 'job_id' => 2]);
    $response = $controller->storeProfileStage($request(), 3280);
    check($response['stage']['job_id'] === 1, 'Stage must use the candidate job, not a submitted job ID');
    check($response['stage']['position'] === 8, 'Append to this job pipeline only');
    check(end(\App\ApplicationStatus::$rows)['status'] === 'Reference check', 'Normalize whitespace');
    check(array_slice(\App\ApplicationStatus::$rows, 0, 4) === $oldStages, 'Do not rewrite existing stages');
    check(\App\JobApplication::$rows === $candidatesBefore, 'Do not move or modify candidates on creation');
    check(in_array(\App\Job::class, $GLOBALS['stageLocks'], true), 'Lock the job for concurrent creation');
    check(in_array('not_regex:/[<>]/', $GLOBALS['stageValidationRules']['status_name'], true), 'Job labels must reject HTML');
    reject(fn () => $controller->storeProfileStage($request(' reference CHECK '), 3280), 'already exists');
    check(count(\App\ApplicationStatus::$rows) === 5, 'Duplicates must not create extra rows');
    $response = $controller->storeProfileStage($request('Reference check'), 3281);
    check($response['stage']['job_id'] === 2 && $response['stage']['position'] === 21, 'Same stage name is allowed on another job, including archived candidates');
    reject(fn () => $controller->storeProfileStage($request(), 3282), 'Assign this candidate');
    reject(fn () => $controller->storeProfileStage($request(), 9999), 'Not found');
    foreach ([['edit_job_applications'], ['edit_jobs'], []] as $permissions) {
        $controller->allow($permissions);
        reject(fn () => $controller->storeProfileStage($request('New stage'), 3280), 'Forbidden');
    }
    check(count(\App\ApplicationStatus::$rows) === 6, 'Rejected requests must leave pipelines intact');
    echo "PASS: current job scope, pipeline ordering, duplicate prevention, archived candidates, and permissions\n";
}
