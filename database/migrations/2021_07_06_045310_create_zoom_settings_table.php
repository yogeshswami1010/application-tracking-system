<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\ZoomSetting;

class CreateZoomSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zoom_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('api_key', 50)->nullable();
            $table->string('secret_key', 50)->nullable();
            $table->string('purchase_code')->nullable();
            $table->timestamp('supported_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zoom_settings');
    }
}
