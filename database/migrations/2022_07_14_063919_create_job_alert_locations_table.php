<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobAlertLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_alert_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('job_alert_id');
            $table->foreign('job_alert_id')->references('id')->on('job_alerts')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('location_id');
            $table->foreign('location_id')->references('id')->on('job_locations')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_alert_locations');
    }
}
