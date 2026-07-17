<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplicantProfileLookupIndexes extends Migration
{
    public function up()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->index(['email', 'is_candidate', 'created_at'], 'ja_email_candidate_created_idx');
        });

        Schema::table('job_application_answers', function (Blueprint $table) {
            $table->index(['job_application_id', 'job_id'], 'jaa_application_job_idx');
        });

        Schema::table('job_client_notes', function (Blueprint $table) {
            $table->index(['job_id', 'created_at'], 'jcn_job_created_idx');
        });

        Schema::table('job_application_status_histories', function (Blueprint $table) {
            $table->index(['job_application_id', 'created_at'], 'jash_application_created_idx');
        });
    }

    public function down()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex('ja_email_candidate_created_idx');
        });

        Schema::table('job_application_answers', function (Blueprint $table) {
            $table->dropIndex('jaa_application_job_idx');
        });

        Schema::table('job_client_notes', function (Blueprint $table) {
            $table->dropIndex('jcn_job_created_idx');
        });

        Schema::table('job_application_status_histories', function (Blueprint $table) {
            $table->dropIndex('jash_application_created_idx');
        });
    }
}
