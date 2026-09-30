<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('candidate_origin', 40)->nullable();
            $table->unsignedInteger('origin_user_id')->nullable();
        });
        Schema::create('candidate_profile_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_application_id')->index();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('action', 40);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('candidate_profile_activities');
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['candidate_origin', 'origin_user_id']);
        });
    }
};
