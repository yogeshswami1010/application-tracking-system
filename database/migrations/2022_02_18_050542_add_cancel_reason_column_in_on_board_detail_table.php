<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelReasonColumnInOnBoardDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('on_board_details', function (Blueprint $table) {
            $table->longText('cancel_reason')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('on_board_details', function (Blueprint $table) {
            $table->dropColumn('cancel_reason');
        });
    }
}
