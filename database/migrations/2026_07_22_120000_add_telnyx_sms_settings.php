<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_settings', function (Blueprint $table) {
            $table->string('sms_provider', 20)->default('vonage')->after('nexmo_status');
            $table->text('telnyx_api_key')->nullable()->after('nexmo_from');
            $table->string('telnyx_from_number', 30)->nullable()->after('telnyx_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('sms_settings', function (Blueprint $table) {
            $table->dropColumn(['sms_provider', 'telnyx_api_key', 'telnyx_from_number']);
        });
    }
};
