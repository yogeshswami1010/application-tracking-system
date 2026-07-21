<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $defaults = [
        ['status' => 'CSS Phone Screen', 'color' => '#2563EB', 'position' => 1],
        ['status' => 'Client Reviewed', 'color' => '#7C3AED', 'position' => 2],
        ['status' => 'Interview', 'color' => '#D97706', 'position' => 3],
        ['status' => 'Hired', 'color' => '#059669', 'position' => 4],
        ['status' => 'Rejected', 'color' => '#DC2626', 'position' => 5],
    ];

    public function up(): void
    {
        if (!Schema::hasColumn('application_status', 'job_id')) {
            Schema::table('application_status', function (Blueprint $table) {
                $table->unsignedInteger('job_id')->nullable()->after('id')->index();
                $table->foreign('job_id')->references('id')->on('jobs')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }

        $applicantCountBefore = DB::table('job_applications')->count();
        $assignedCountBefore = DB::table('job_applications')->whereNotNull('status_id')->count();

        DB::transaction(function () use ($applicantCountBefore, $assignedCountBefore) {
            $globalStatuses = DB::table('application_status')
                ->whereNull('job_id')
                ->orderBy('position')
                ->orderBy('id')
                ->get()
                ->keyBy('id');

            foreach (DB::table('jobs')->orderBy('id')->pluck('id') as $jobId) {
                $job = (object) ['id' => $jobId];
                $statusMap = [];

                // Copy every status currently used by this job first. This is
                // intentionally additive so no applicant can lose its stage.
                $usedStatusIds = DB::table('job_applications')
                    ->where('job_id', $job->id)
                    ->whereNotNull('status_id')
                    ->distinct()
                    ->pluck('status_id');

                foreach ($usedStatusIds as $oldStatusId) {
                    $old = $globalStatuses->get($oldStatusId)
                        ?? DB::table('application_status')->where('id', $oldStatusId)->first();
                    if (!$old) {
                        continue;
                    }

                    $canonical = $this->canonicalDefault((string) $old->status);
                    $name = $canonical['status'] ?? $old->status;
                    $newId = $this->ensureJobStatus(
                        (int) $job->id,
                        $name,
                        $canonical['color'] ?? ($old->color ?: '#6B7280'),
                        $canonical['position'] ?? ((int) $old->position ?: 50)
                    );
                    $statusMap[(int) $oldStatusId] = $newId;
                }

                foreach ($this->defaults as $default) {
                    $this->ensureJobStatus((int) $job->id, $default['status'], $default['color'], $default['position']);
                }

                foreach ($statusMap as $oldStatusId => $newStatusId) {
                    DB::table('job_applications')
                        ->where('job_id', $job->id)
                        ->where('status_id', $oldStatusId)
                        ->update(['status_id' => $newStatusId]);
                }
            }

            if (DB::table('job_applications')->count() !== $applicantCountBefore
                || DB::table('job_applications')->whereNotNull('status_id')->count() !== $assignedCountBefore) {
                throw new RuntimeException('Pipeline migration safety check failed; applicant changes were rolled back.');
            }
        });
    }

    public function down(): void
    {
        // Move applicants back to an equivalent global status before removing
        // job statuses. Rollback therefore remains data-safe as well.
        DB::transaction(function () {
            $jobStatuses = DB::table('application_status')->whereNotNull('job_id')->get();
            foreach ($jobStatuses as $status) {
                $globalId = DB::table('application_status')
                    ->whereNull('job_id')
                    ->whereRaw('LOWER(status) = ?', [mb_strtolower($status->status)])
                    ->value('id');
                if (!$globalId) {
                    $globalId = DB::table('application_status')->insertGetId([
                        'job_id' => null,
                        'status' => $status->status,
                        'color' => $status->color,
                        'position' => $status->position,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                DB::table('job_applications')->where('status_id', $status->id)->update(['status_id' => $globalId]);
            }
            DB::table('application_status')->whereNotNull('job_id')->delete();
        });

        Schema::table('application_status', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
            $table->dropColumn('job_id');
        });
    }

    private function ensureJobStatus(int $jobId, string $name, string $color, int $position): int
    {
        $normalName = mb_strtolower(trim($name));
        $aliases = $normalName === 'css phone screen'
            ? ['css phone screen', 'phone screen']
            : [$normalName];
        $existing = DB::table('application_status')
            ->where('job_id', $jobId)
            ->whereIn(DB::raw('LOWER(status)'), $aliases)
            ->value('id');

        if ($existing) {
            if ($normalName === 'css phone screen') {
                DB::table('application_status')->where('id', $existing)->update([
                    'status' => $name,
                    'color' => $color,
                    'position' => $position,
                    'updated_at' => now(),
                ]);
            }
            return (int) $existing;
        }

        return (int) DB::table('application_status')->insertGetId([
            'job_id' => $jobId,
            'status' => trim($name),
            'color' => $color,
            'position' => $position,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function canonicalDefault(string $name): ?array
    {
        $normal = mb_strtolower(trim(preg_replace('/\s+/', ' ', $name)));
        $aliases = [
            'phone screen' => 'CSS Phone Screen',
            'css phone screen' => 'CSS Phone Screen',
            'client reviewed' => 'Client Reviewed',
            'interview' => 'Interview',
            'hired' => 'Hired',
            'rejected' => 'Rejected',
        ];
        if (!isset($aliases[$normal])) return null;
        $canonical = $aliases[$normal];
        foreach ($this->defaults as $default) {
            if ($default['status'] === $canonical) return $default;
        }
        return null;
    }
};
