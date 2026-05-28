<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobOnboardQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_onboard_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('question_id');

            $table->foreign('question_id')->references('id')->on('job_offer_questions')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('job_offer_id');

            $table->foreign('job_offer_id')->references('id')->on('on_board_details')
                ->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('job_onboard_questions');
    }
}
