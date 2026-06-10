<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Job;

class JobController extends Controller
{
    // ── Consortium jobs (show_on_consortium = true) ───────────────
    public function allJobs()
    {
        $jobs = Job::with([
            'company',
            'category',
            'jobType',
            'experience',
            'jobLocation',
            'currency'
        ])
        ->where('status', 'active')
        ->where('show_on_consortium', true)
        ->orderBy('created_at', 'desc')
        ->get();

        $data = $this->formatJobs($jobs, 'consortium');

        return response()->json([
            'status'     => true,
            'total_jobs' => count($data),
            'jobs'       => $data,
        ]);
    }

    // ── AssistMyDay jobs (show_on_assistmyday = true) ─────────────
    public function assistMyDayJobs()
    {
        $jobs = Job::with([
            'company',
            'category',
            'jobType',
            'experience',
            'jobLocation',
            'currency'
        ])
        ->where('status', 'active')
        ->where('show_on_assistmyday', true)
        ->orderBy('created_at', 'desc')
        ->get();

        $data = $this->formatJobs($jobs, 'assistmyday');

        return response()->json([
            'status'     => true,
            'total_jobs' => count($data),
            'jobs'       => $data,
        ]);
    }

    // ── Shared formatter ──────────────────────────────────────────
    private function formatJobs($jobs, string $source = 'consortium'): array
    {
        $data = [];

        foreach ($jobs as $job) {

            // Salary
            $salary = 'N/A';

            if ($job->show_salary) {
                $sym = $job->currency->currency_symbol ?? '$';

                if ($job->pay_type === 'Range') {
                    $salary = $sym . number_format($job->starting_salary)
                        . ' - '
                        . $sym . number_format($job->maximum_salary);

                } elseif ($job->pay_type === 'Maximum') {
                    $salary = $sym . number_format($job->maximum_salary);

                } else {
                    // Starting, Exact Amount, or anything else
                    $salary = $sym . number_format($job->starting_salary);
                }

                if (!empty($job->pay_according)) {
                    $salary .= ' / ' . $job->pay_according;
                }
            }

            // Experience
            $experience = 'N/A';
            if ($job->experience) {
                $experience = $job->experience->name
                    ?? $job->experience->work_experience
                    ?? 'N/A';
            }

            // Apply URL — AssistMyDay gets its own detail page
            $firstLocation = optional($job->jobLocation->first());

            if ($source === 'assistmyday') {
                $applyUrl = route('jobs.assistmyday.jobDetail', [
                    $job->slug,
                    $firstLocation->id,
                ]);
            } else {
                $applyUrl = route('jobs.jobDetail', [
                    $job->slug,
                    $firstLocation->id,
                ]);
            }

            $data[] = [
                'id'          => $job->id,
                'job_title'   => $job->title,
                'slug'        => $job->slug,
                'location'    => $job->jobLocation->pluck('location')->implode(', '),
                'category'    => $job->category->name    ?? '',
                'company'     => $job->company->company_name ?? '',
                'job_type'    => $job->jobType->job_type ?? '',
                'salary'      => $salary,
                'experience'  => $experience,
                'apply_url'   => $applyUrl,
                'source'      => $source,
            ];
        }

        return $data;
    }
}