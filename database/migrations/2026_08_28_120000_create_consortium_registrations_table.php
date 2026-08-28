<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consortium_registrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();
            $table->string('phone', 40);
            $table->string('gender', 40)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('street_address')->nullable();
            $table->string('city')->nullable();
            $table->boolean('eligible_to_work_canada');
            $table->string('status_in_canada')->nullable();
            $table->string('preferred_job_type')->nullable();
            $table->string('commute_mode')->nullable();
            $table->decimal('years_experience', 5, 1)->nullable();
            $table->string('industry_expertise')->nullable();
            $table->boolean('available_weekends')->nullable();
            $table->boolean('available_night_shifts')->nullable();
            $table->string('referral_source')->nullable();
            $table->text('additional_information')->nullable();
            $table->string('resume_file')->nullable();
            $table->string('resume_original_name')->nullable();
            $table->boolean('information_certified');
            $table->boolean('agreement_accepted');
            $table->boolean('sms_consent')->default(false);
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consortium_registrations');
    }
};