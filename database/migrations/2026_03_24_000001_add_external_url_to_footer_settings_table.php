<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->string('external_url', 2048)->nullable();
            $table->string('link_target', 10)->default('_self');
        });
    }

    public function down(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn(['external_url', 'link_target']);
        });
    }
};
