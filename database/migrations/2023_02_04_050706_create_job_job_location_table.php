<?php
use App\Job;
use App\JobLocation;
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
        Schema::create('job_job_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_id');
            $table->unsignedInteger('location_id')->nullable();
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('location_id')->references('id')->on('job_locations')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
        $jobs = Job::all();
        foreach($jobs as $job)
        {
            $jobLocation = JobLocation::where('id', $job->location_id);
            $jobLocation = new JobJobLocation;
            $jobLocation->job_id = $job->id;
            $jobLocation->location_id = $job->location_id;
            $jobLocation->save();
        }
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_job_location');
    }
};