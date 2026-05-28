<?php

use App\JobApplication;
use App\JobJobLocation;
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
            $table->unsignedInteger('location_id')->after('status_id')->nullable();
            $table->foreign('location_id')->references('id')->on('job_locations')->onDelete('cascade')->onUpdate('cascade');
        });

        $JobApplications = JobApplication::all();
        foreach($JobApplications as $jobApplication) {
            $jobLocation = JobJobLocation::where('job_id', $jobApplication->job_id)->first();
            // dd($jobLocation);
            // $jobApplication = JobApplication::find($jobApplication->id);
            if($jobLocation && $jobApplication)
            {
                $jobApplication->location_id = $jobLocation->location_id;
                $jobApplication->save();
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
            //
        });
    }
};
