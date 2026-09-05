<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('consortium_registration_job_moves as moves')
            ->join('job_applications as applications', 'applications.id', '=', 'moves.job_application_id')
            ->whereNull('applications.location_id')
            ->select('moves.job_application_id', 'moves.job_id')
            ->orderBy('moves.id')
            ->get()
            ->each(function ($move) {
                $locationId = DB::table('job_job_locations')
                    ->where('job_id', $move->job_id)
                    ->whereNotNull('location_id')
                    ->orderBy('id')
                    ->value('location_id');

                if (!$locationId) {
                    $locationId = DB::table('jobs')->where('id', $move->job_id)->value('location_id');
                }

                if ($locationId) {
                    DB::table('job_applications')
                        ->where('id', $move->job_application_id)
                        ->whereNull('location_id')
                        ->update(['location_id' => $locationId]);
                }
            });
    }

    public function down(): void
    {
        // Data repair only: do not remove valid applicant locations on rollback.
    }
};