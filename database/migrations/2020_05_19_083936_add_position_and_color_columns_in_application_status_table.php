<?php

use App\ApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPositionAndColorColumnsInApplicationStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_status', function (Blueprint $table) {
            $table->smallInteger('position')->after('status')->nullable();
            $table->string('color', 12)->after('position')->nullable();
        });

        $statuses = ApplicationStatus::select('id', 'status', 'position', 'color')->get();

        $position = [1, 2, 3, 4, 5, 6];
        $color = ['#2b2b2b', '#f1e52e', '#3d8ee8', '#32ac16', '#ee1127', '#FF8C00'];

        foreach ($statuses as $key => $status) {
            $status->position = $position[$key];
            $status->color = $color[$key];

            $status->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_status', function (Blueprint $table) {
            $table->dropColumn('position');
            $table->dropColumn('color');
        });
    }
}
