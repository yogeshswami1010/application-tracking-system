<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminAtsSyncController extends Controller
{
    /**
     * Return a small fingerprint of shared ATS data. Browsers compare this value
     * to detect changes made by another signed-in team member.
     */
    public function state(): JsonResponse
    {
        $tables = [
            'jobs',
            'job_applications',
            'application_status',
            'applicant_notes',
            'job_client_notes',
            'applicant_sms_messages',
            'interview_schedules',
            'job_application_answers',
            'job_application_status_history',
            'companies',
            'job_locations',
        ];

        // Schema lookups are cached because this endpoint is intentionally polled.
        $metadata = Cache::remember('ats_sync_table_metadata_v1', 3600, function () use ($tables) {
            return collect($tables)->mapWithKeys(function ($table) {
                if (!Schema::hasTable($table)) {
                    return [];
                }

                return [$table => [
                    'updated_at' => Schema::hasColumn($table, 'updated_at'),
                    'deleted_at' => Schema::hasColumn($table, 'deleted_at'),
                ]];
            })->all();
        });

        $state = [];
        foreach ($metadata as $table => $columns) {
            $select = ['COUNT(*) AS row_count'];
            if ($columns['updated_at']) $select[] = 'MAX(updated_at) AS last_updated_at';
            if ($columns['deleted_at']) $select[] = 'MAX(deleted_at) AS last_deleted_at';

            $state[$table] = (array) DB::table($table)->selectRaw(implode(', ', $select))->first();
        }

        return response()->json([
            'signature' => hash('sha256', json_encode($state)),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}