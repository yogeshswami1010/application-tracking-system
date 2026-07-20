<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('jobs', function (Blueprint $table) { $table->string('job_code', 100)->nullable()->index()->after('title'); }); }
    public function down(): void { Schema::table('jobs', function (Blueprint $table) { $table->dropIndex(['job_code']); $table->dropColumn('job_code'); }); }
};
