@extends('layouts.app')

@section('page-title-html')
    ATS <em>Overview</em>
@endsection

@section('page-subtitle')
    Live applicant totals and pipeline activity for every active job.
@endsection

@section('content')
    <div class="mb-5 grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-[#E3E7EE] bg-white p-5 shadow-sm">
            <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-[#8892A0]">Active Jobs</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-[#1A1E2E]">{{ number_format($activeJobCount) }}</p>
        </div>
        <div class="rounded-2xl border border-[#E3E7EE] bg-white p-5 shadow-sm">
            <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-[#8892A0]">Applicants in Active Jobs</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-[#1A1E2E]">{{ number_format($activeApplicantCount) }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#E3E7EE] bg-white shadow-sm">
        <div class="border-b border-[#E9ECF1] px-5 py-4">
            <h2 class="text-[16px] font-bold text-[#1A1E2E]">Active Job Pipeline</h2>
            <p class="mt-1 text-[12.5px] text-[#697386]">Click a job to open only its applicants in Table View.</p>
        </div>

        @forelse($jobs as $job)
            <a href="{{ route('admin.job-applications.table', ['jobs' => $job->id, 'from' => 'ats-overview']) }}"
               class="group block border-b border-[#EEF0F4] px-5 py-5 transition-colors last:border-b-0 hover:bg-[#F8FAFD]">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="min-w-0 xl:w-1/3">
                        <div class="flex items-center gap-2">
                            <h3 class="truncate text-[15px] font-bold text-[#1A1E2E] group-hover:text-[#2563EB]">{{ $job->title }}</h3>
                            <span class="shrink-0 text-[#A0A8B5] group-hover:text-[#2563EB]">&rsaquo;</span>
                        </div>
                        <p class="mt-1 text-[12.5px] text-[#697386]">
                            {{ optional($job->company)->company_name ?: 'No company' }}
                            <span class="mx-1.5 text-[#CBD1DA]">&bull;</span>
                            Ends {{ optional($job->end_date)->format('M d, Y') }}
                        </p>
                    </div>

                    <div class="flex flex-1 flex-wrap items-center gap-2">
                        @foreach($job->statuses as $status)
                            <span class="inline-flex items-center gap-2 rounded-full border border-[#E3E7EE] bg-white px-3 py-1.5 text-[11.5px] font-semibold text-[#4D586B]">
                                <span class="h-2 w-2 rounded-full" style="background-color: {{ $status->color ?: '#6B7280' }}"></span>
                                {{ ucwords(str_replace(['_', '-'], ' ', $status->status)) }}
                                <strong class="text-[#1A1E2E]">{{ number_format($status->applicant_count) }}</strong>
                            </span>
                        @endforeach
                    </div>

                    <div class="shrink-0 rounded-xl bg-[#EEF4FF] px-4 py-2 text-center">
                        <p class="text-[10.5px] font-semibold uppercase tracking-[0.06em] text-[#6A7A92]">Applicants</p>
                        <p class="mt-0.5 text-xl font-bold text-[#2563EB]">{{ number_format($job->applicant_count) }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="px-6 py-16 text-center">
                <p class="text-[15px] font-semibold text-[#3D4A5C]">No active jobs</p>
                <p class="mt-1 text-[12.5px] text-[#8892A0]">Active jobs within their start and end dates will appear here.</p>
            </div>
        @endforelse
    </div>
@endsection
