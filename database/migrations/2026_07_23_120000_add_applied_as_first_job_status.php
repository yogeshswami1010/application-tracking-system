<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('jobs')->orderBy('id')->pluck('id')->each(function ($jobId) {
            DB::transaction(function () use ($jobId) {
                $statuses = DB::table('application_status')
                    ->where('job_id', $jobId)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->get();

                $applied = $statuses->first(fn ($status) => mb_strtolower(trim($status->status)) === 'applied');
                if ($applied) {
                    DB::table('application_status')->where('id', $applied->id)->update([
                        'status' => 'Applied',
                        'color' => '#2563EB',
                        'position' => 1,
                    ]);
                    $appliedId = $applied->id;
                } else {
                    $appliedId = DB::table('application_status')->insertGetId([
                        'job_id' => $jobId,
                        'status' => 'Applied',
                        'color' => '#2563EB',
                        'position' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $position = 2;
                foreach ($statuses->where('id', '!=', $appliedId) as $status) {
                    DB::table('application_status')->where('id', $status->id)->update(['position' => $position++]);
                }
            });
        });
    }

    public function down(): void
    {
        // Intentionally preserve status rows and applicant status references.
    }
};
