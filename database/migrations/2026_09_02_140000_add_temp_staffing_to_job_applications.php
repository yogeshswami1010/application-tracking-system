<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->boolean('is_temp_staffing')->default(false)->index()->after('is_marketing');
            $table->timestamp('temp_staffing_at')->nullable()->after('is_temp_staffing');
            $table->unsignedInteger('temp_staffing_by')->nullable()->after('temp_staffing_at');
        });

        Schema::create('job_application_temp_staffing_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_application_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('action', 20);
            $table->timestamps();
            $table->foreign('job_application_id', 'jatsh_application_fk')->references('id')->on('job_applications')->cascadeOnDelete();
            $table->foreign('user_id', 'jatsh_user_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['job_application_id', 'created_at'], 'jatsh_application_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_application_temp_staffing_histories');
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex(['is_temp_staffing']);
            $table->dropColumn(['is_temp_staffing', 'temp_staffing_at', 'temp_staffing_by']);
        });
    }
};