<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consortium_registrations', function (Blueprint $table) {
            $table->string('legacy_source', 50)->nullable()->after('id');
            $table->string('legacy_id', 100)->nullable()->after('legacy_source');
            $table->string('legacy_experience_text')->nullable()->after('years_experience');
            $table->unique(['legacy_source', 'legacy_id'], 'consortium_registrations_legacy_unique');
        });
    }

    public function down(): void
    {
        Schema::table('consortium_registrations', function (Blueprint $table) {
            $table->dropUnique('consortium_registrations_legacy_unique');
            $table->dropColumn(['legacy_source', 'legacy_id', 'legacy_experience_text']);
        });
    }
};