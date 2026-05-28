<?php

use App\Job;
use App\JobApplication;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        
        Schema::table('job_applications', function (Blueprint $table) {
            $table->text('address')->nullable()->after('phone');
        });

        $jobs = Job::all();

        foreach($jobs as $job)
        {
            if(is_null($job->required_columns->address))
            {
                $array = $job->required_columns;
                $array['address'] = false;
                $job->required_columns = $array;
                $job->save();
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }

};
