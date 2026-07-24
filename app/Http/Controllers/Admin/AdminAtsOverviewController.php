<?php

namespace App\Http\Controllers\Admin;

use App\Job;
use App\JobApplication;
use Carbon\Carbon;

class AdminAtsOverviewController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->pageIcon = 'icon-chart';
        $this->pageTitle = 'ATS Overview';
    }

    public function index()
    {
        abort_if(! $this->user->cans('view_job_applications'), 403);

        $today = Carbon::today()->toDateString();
        $jobs = Job::query()
            ->select('id', 'title', 'company_id', 'start_date', 'end_date', 'status')
            ->with([
                'company:id,company_name',
                'statuses' => function ($query) {
                    $query->select('id', 'job_id', 'status', 'color', 'position')
                        ->orderBy('position');
                },
            ])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderBy('end_date')
            ->orderBy('title')
            ->get();

        $jobIds = $jobs->pluck('id');
        $counts = collect();

        if ($jobIds->isNotEmpty()) {
            // Match the table view's merged-profile rule: only the newest
            // application for each email is shown and counted.
            $counts = JobApplication::query()
                ->selectRaw('job_id, status_id, COUNT(*) as applicant_count')
                ->where('is_candidate', 0)
                ->whereIn('job_id', $jobIds)
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(ja2.id)')
                        ->from('job_applications as ja2')
                        ->where('ja2.is_candidate', 0)
                        ->whereNull('ja2.deleted_at')
                        ->groupBy('ja2.email');
                })
                ->groupBy('job_id', 'status_id')
                ->get()
                ->groupBy('job_id');
        }

        $jobs->each(function (Job $job) use ($counts) {
            $jobCounts = $counts->get($job->id, collect())->keyBy('status_id');

            $job->statuses->each(function ($status) use ($jobCounts) {
                $status->applicant_count = (int) optional($jobCounts->get($status->id))->applicant_count;
            });

            $job->applicant_count = (int) $job->statuses->sum('applicant_count');
        });

        $this->jobs = $jobs;
        $this->activeJobCount = $jobs->count();
        $this->activeApplicantCount = (int) $jobs->sum('applicant_count');

        return view('admin.ats-overview.index', $this->data);
    }
}
