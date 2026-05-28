<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnJobAlertStatusInCompanySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->boolean('job_alert_status')->default(1)->after('front_language');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_settings', function (Blueprint $table) {
            //
        });
    }
}
