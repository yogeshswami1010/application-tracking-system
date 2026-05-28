<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Job;

class JobController extends Controller
{
    public function allJobs()
    {
        $jobs = Job::with(['company', 'category', 'jobType', 'experience', 'jobLocation'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [];

        foreach ($jobs as $job) {

            $salary = 'N/A';

            if ($job->show_salary == 1) {

                if ($job->pay_type == 'Range') {
                    $salary = '' . $job->starting_salary . ' - ' . $job->maximum_salary;
                } else {
                    $salary = '' . $job->starting_salary;
                }

                if (!empty($job->pay_according)) {
                    $salary .= ' / ' . $job->pay_according;
                }
            }

            $experience = 'N/A';

            if ($job->experience) {
                $experience = $job->experience->name ?? $job->experience->work_experience ?? 'N/A';
            }

            $data[] = [
                'id'          => $job->id,
                'job_title'   => $job->title,
                'slug'        => $job->slug,
                'location' => $job->jobLocation->pluck('location')->implode(', '),
                'category'    => $job->category->name ?? '',
                'company'     => $job->company->company_name ?? '',
                'job_type'    => $job->jobType->job_type ?? '',
                'salary'      => $salary,
                'experience'  => $experience,
                'apply_url' => route( 'jobs.jobDetail', [ $job->slug, optional($job->jobLocation->first())->id ] ),
            ];
        }

        return response()->json([
            'status'      => true,
            'total_jobs'  => count($data),
            'jobs'        => $data
        ]);
    }
}