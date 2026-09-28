<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidate_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('job_application_id');
            $table->foreign('job_application_id')->references('id')->on('job_applications')->cascadeOnDelete();
            $table->unsignedInteger('user_id')->index();
            $table->string('phone', 32);
            $table->string('status', 32)->default('initiated');
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamp('recording_consent_at')->nullable();
            $table->string('audio_path')->nullable();
            $table->longText('transcript')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('candidate_calls'); }
};
