<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('language_settings', 'text_direction')) {
            return;
        }

        Schema::table('language_settings', function (Blueprint $table) {
            $table->dropColumn('text_direction');
        });
    }

    public function down(): void
    {
        Schema::table('language_settings', function (Blueprint $table) {
            $table->string('text_direction', 3)->default('ltr');
        });
    }
};
