<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zoom_settings', function (Blueprint $table) {
            $table->string('account_id', 50)->after('id')->nullable();
            $table->string('meeting_sdk_api_key', 50)->after('secret_token')->nullable();
            $table->string('meeting_sdk_api_secret', 50)->after('meeting_sdk_api_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zoom_settings', function (Blueprint $table) {
            $table->dropColumn(['account_id', 'meeting_sdk_api_key', 'meeting_sdk_api_secret']);
        });
    }
};
