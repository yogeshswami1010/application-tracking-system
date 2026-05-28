<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->unsignedBigInteger('work_experience_id');
            $table->foreign('work_experience_id')->references('id')->on('work_experiences')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('job_type_id');
            $table->foreign('job_type_id')->references('id')->on('job_types')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('hash');
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
        Schema::dropIfExists('job_alerts');
    }
}
