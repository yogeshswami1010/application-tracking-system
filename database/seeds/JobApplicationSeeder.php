<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class JobApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\JobApplication::factory()->count(20)->create();

        $jobs = \App\Job::select('id', 'title', 'job_description', 'meta_details')->get();
        
        foreach ($jobs as $job) {
            $job->meta_details = [
                'title' => $job->title,
                'description' => strip_tags(Str::substr(html_entity_decode($job->job_description), 0, 150))
            ];

            $job->section_visibility = [
                'profile_image' =>'no',
                'resume' => 'yes',
                'cover_letter' => 'yes',
                'terms_and_conditions' => 'yes'
            ];

            $job->save();
        }
    }
}
