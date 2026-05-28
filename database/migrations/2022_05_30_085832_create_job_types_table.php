<?php

use App\JobType;
use App\WorkExperience;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_types', function (Blueprint $table) {
            $table->id();
            $table->string('job_type');
            $table->timestamps();
        });

        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->string('work_experience');
            $table->timestamps();
        });


        $jobType = new JobType();
        $jobType->job_type = "Full time";
        $jobType->save();

        $jobType = new JobType();
        $jobType->job_type = "Part time";
        $jobType->save();

        $jobType = new JobType();
        $jobType->job_type = "On Contract";
        $jobType->save();

        $jobType = new JobType();
        $jobType->job_type = "Internship";
        $jobType->save();

        $jobType = new JobType();
        $jobType->job_type = "Trainee";
        $jobType->save();


        $workExperience = new WorkExperience;
        $workExperience->work_experience = "Fresher";
        $workExperience->save();

        $workExperience = new WorkExperience;
        $workExperience->work_experience = "0-1 years";
        $workExperience->save();

        $workExperience = new WorkExperience;
        $workExperience->work_experience = "1-3 years";
        $workExperience->save();

        $workExperience = new WorkExperience;
        $workExperience->work_experience = "3-5 years";
        $workExperience->save();

        $workExperience = new WorkExperience;
        $workExperience->work_experience = "5+ years";
        $workExperience->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_types');
        Schema::dropIfExists('work_experiences');

    }
}
