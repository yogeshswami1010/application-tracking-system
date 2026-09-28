<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            // Null preserves existing environment configuration until first saved.
            $table->boolean('candidate_calls_enabled')->nullable();
            $table->string('telnyx_voice_credential_id', 191)->nullable();
            $table->string('telnyx_voice_from_number', 32)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['candidate_calls_enabled', 'telnyx_voice_credential_id', 'telnyx_voice_from_number']);
        });
    }
};
