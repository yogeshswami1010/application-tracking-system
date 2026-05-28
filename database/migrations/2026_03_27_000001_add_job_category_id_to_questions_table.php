<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (! Schema::hasColumn('questions', 'job_category_id')) {
                // job_categories.id is created via increments() => unsigned integer.
                $table->unsignedInteger('job_category_id')->nullable()->after('id');
                $table->foreign('job_category_id')->references('id')->on('job_categories')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'job_category_id')) {
                $table->dropForeign(['job_category_id']);
                $table->dropColumn('job_category_id');
            }
        });
    }
};
