<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColomnInJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('job_type_id')->after('category_id')->nullable();
            $table->unsignedBigInteger('work_experience_id')->after('job_type_id')->nullable();
            $table->string('pay_type')->after('work_experience_id')->nullable();
            $table->double('starting_salary')->after('work_experience_id')->nullable();
            $table->double('maximum_salary')->after('starting_salary')->nullable();;
            $table->string('pay_according')->after('maximum_salary')->nullable();
            $table->boolean('show_work_experience')->after('pay_according')->default(false);
            $table->boolean('show_salary')->after('show_work_experience')->default(false);
            $table->boolean('show_job_type')->after('show_salary')->default(false);
            $table->foreign('job_type_id')->references('id')->on('job_types')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('work_experience_id')->references('id')->on('work_experiences')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            // 
        });
    }
}
