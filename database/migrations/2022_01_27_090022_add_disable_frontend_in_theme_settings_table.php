<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisableFrontendInThemeSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->boolean('disable_frontend')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn(['disable_frontend']);
        });
    }
}
