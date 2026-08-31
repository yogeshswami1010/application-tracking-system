<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('hashname');
        });

        Schema::create('job_application_resume_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_application_id');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('hashname');
            $table->string('original_name')->nullable();
            $table->timestamps();
            $table->index(['job_application_id', 'created_at'], 'ja_resume_history_app_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_application_resume_histories');
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('original_name');
        });
    }
};
