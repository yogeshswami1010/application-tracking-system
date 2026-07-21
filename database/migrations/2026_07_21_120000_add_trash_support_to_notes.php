<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Soft deletion is also used by the existing Candidate Database archive.
            $table->timestamp('moved_to_trash_at')->nullable()->after('deleted_at')->index();
        });

        Schema::table('applicant_notes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('job_client_notes', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('moved_to_trash_at');
        });

        Schema::table('applicant_notes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('job_client_notes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
