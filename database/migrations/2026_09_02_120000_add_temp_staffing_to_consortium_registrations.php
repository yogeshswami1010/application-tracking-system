<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consortium_registrations', function (Blueprint $table) {
            $table->boolean('is_temp_staffing')->default(false)->index()->after('reviewed_at');
            $table->timestamp('temp_staffing_at')->nullable()->after('is_temp_staffing');
            $table->unsignedBigInteger('temp_staffing_by')->nullable()->after('temp_staffing_at');
        });
    }

    public function down(): void
    {
        Schema::table('consortium_registrations', function (Blueprint $table) {
            $table->dropIndex(['is_temp_staffing']);
            $table->dropColumn(['is_temp_staffing', 'temp_staffing_at', 'temp_staffing_by']);
        });
    }
};