<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consortium_registration_job_moves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consortium_registration_id');
            $table->unsignedInteger('job_application_id');
            $table->unsignedInteger('job_id');
            $table->unsignedBigInteger('moved_by')->nullable();
            $table->timestamps();
            $table->unique(['consortium_registration_id', 'job_id'], 'consortium_registration_job_unique');
            $table->index('job_application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consortium_registration_job_moves');
    }
};
