<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnboardAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onboard_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onboard_id');

            $table->foreign('onboard_id')->references('id')->on('on_board_details')
                ->onUpdate('cascade')->onDelete('cascade');
            

            $table->unsignedInteger('question_id');

            $table->foreign('question_id')->references('id')->on('job_offer_questions')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->mediumText('answer')->nullable()->default(null);
            $table->string('file')->nullable();
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
        Schema::dropIfExists('onboard_answers');
    }
}
