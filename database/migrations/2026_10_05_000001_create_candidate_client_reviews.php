<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidate_client_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('public_id')->unique();
            $table->unsignedInteger('job_application_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('client_email');
            $table->string('subject', 191);
            $table->string('candidate_name');
            $table->string('job_title')->nullable();
            $table->string('resume_hashname');
            $table->string('resume_original_name');
            $table->longText('body_html');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->foreign('job_application_id')->references('id')->on('job_applications')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['job_application_id', 'created_at']);
        });
        Schema::create('candidate_client_review_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('candidate_client_review_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->uuid('submission_id');
            $table->string('direction', 10);
            $table->longText('body_html')->nullable();
            $table->longText('body_text');
            $table->string('mail_status', 10)->default('pending');
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->foreign('candidate_client_review_id', 'client_review_messages_review_fk')->references('id')->on('candidate_client_reviews')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->unique(['candidate_client_review_id', 'submission_id'], 'client_review_submission_unique');
            $table->index(['candidate_client_review_id', 'direction', 'read_at'], 'client_review_unread_index');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('candidate_client_review_messages');
        Schema::dropIfExists('candidate_client_reviews');
    }
};
