<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMeetingAppColumnSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zoom_settings', function (Blueprint $table) {
            $table->string('meeting_app')->default('in_app');
        });
        $data=[
            'api_key' =>null,
            'secret_key' =>null,
            'purchase_code' =>null,
            'supported_until' =>null,
            'purchase_code' =>null,
            'meeting_app' =>'in_app',
        ];
        DB::table('zoom_settings')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zoom_settings', function (Blueprint $table) {
            $table->dropColumn(['meeting_app']);
        });
    }
}
