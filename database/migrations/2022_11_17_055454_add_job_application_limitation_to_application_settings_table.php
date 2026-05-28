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
        Schema::table('application_settings', function (Blueprint $table) {
            $table->integer('job_application_limitation')->default('0')->after('mail_setting'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_settings', function (Blueprint $table) {
            $table->dropColumn('job_application_limitation');
        });
    }
};
