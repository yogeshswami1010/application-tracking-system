<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobOfferQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_offer_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('question');
            $table->enum('status', ['enable', 'disable'])->default('enable');
            $table->enum('required', ['yes', 'no'])->default('no');
            $table->enum('type', ['text', 'file'])->default('text');
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
        Schema::dropIfExists('job_offer_questions');
    }
}
