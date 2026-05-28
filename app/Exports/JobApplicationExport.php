<?php

namespace App\Exports;

use App\JobApplication;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;

class JobApplicationExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $filters;
    protected $data;

    public function __construct(array $filters, array $data)
    {
        $this->filters = $filters;
        $this->data = $data;
    }

    public function collection()
    {
        // Fetching All Job Applications
        $jobApplications = JobApplication::select(
            'job_applications.id',
            'jobs.title',
            'job_applications.full_name',
            'job_applications.email',
            'job_applications.phone',
            'job_applications.cover_letter',
            'application_status.status',
            'job_applications.created_at'
        )
            ->leftJoin('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->leftJoin('application_status', 'application_status.id', '=', 'job_applications.status_id');
        if ($this->filters['status'] != 'all' && $this->filters['status'] != '') {
            $jobApplications = $jobApplications->where('job_applications.status_id', $this->filters['status']);
        }

        // Filter  By Location
        if ($this->filters['location'] != 'all' && $this->filters['location'] != '') {
            $jobApplications = $jobApplications->where('jobs.location_id', $this->filters['location']);
        }

        // Filter  By Job
        if ($this->filters['jobs'] != 'all' && $this->filters['jobs'] != '') {
            $jobApplications = $jobApplications->where('job_applications.job_id', $this->filters['jobs']);
        }  // Filter  By Job
        if ($this->filters['jobs'] != 'all' && $this->filters['jobs'] != '') {
            $jobApplications = $jobApplications->where('job_applications.job_id', $this->filters['jobs']);
        
        }

        // Filter  By StartDate of job
        if ($this->filters['startDate'] != null && $this->filters['startDate'] != '' && $this->filters['startDate'] != 0) {
            $startDate = $this->filters['startDate'];
            $jobApplications = $jobApplications->whereDate('job_applications.created_at', '>=', "$startDate");
        }

        // Filter  By EndDate of job
        if ($this->filters['endDate'] != null && $this->filters['endDate'] != '' && $this->filters['endDate'] != 0) {
            $endDate = $this->filters['endDate'];

            $jobApplications = $jobApplications->whereDate('job_applications.updated_at', '<=', "$endDate");
        }

        $attributes = ['resume_url', 'photo_url'];
        $jobApplications = $jobApplications->get()->makeHidden($attributes);

        return $jobApplications;
    }

    public function headings(): array
    {
        return ['ID', 'Job Title', 'Name', 'Email', 'Mobile', 'Cover Letter', 'Status', 'Applied at'];
    }

    public function registerEvents(): array
    {
        return [
            BeforeExport::class => function(BeforeExport $event) {
                $event->writer->getProperties()->setTitle('Job Applications')->setDescription('job-applications file')->setCreator('Recruit')->setCompany($this->data['company']);
            },
            AfterSheet::class => function(AfterSheet $event) {
                $styleArray = [
                    'font' => [
                        'bold' => true,
                    ],
                ];
                $event->sheet->getDelegate()->getStyle('A1:H1')->applyFromArray($styleArray);
            },
        ];
    }
}
