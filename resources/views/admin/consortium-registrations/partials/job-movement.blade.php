@if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[12px] font-semibold text-emerald-700">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] font-semibold text-red-700">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] font-semibold text-red-700">{{ $errors->first() }}</div>
@endif

@php
    $movedJobIds = $jobMoves->pluck('job_id')->map(fn ($id) => (int) $id);
@endphp
<div class="rounded-2xl border border-[#DDE6F5] bg-gradient-to-br from-white to-blue-50/50 p-5 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-blue-600">Candidate Placement</p>
            <h3 class="mt-1 text-[16px] font-bold text-[#1A1E2E]">Move candidate to a job</h3>

        </div>
        <form method="POST" action="{{ route('admin.consortium-registrations.move-to-job', $registration) }}" class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:max-w-2xl" onsubmit="return confirm('Move this candidate to the selected job?')">
            @csrf
            <select name="job_id" required class="h-10 min-w-0 flex-1 rounded-xl border border-[#CCD6E5] bg-white px-3 py-0 text-[12px] font-medium text-[#334155] outline-none focus:border-blue-500">
                <option value="">Select a job role...</option>
                @foreach($jobs as $job)
                    @php
                        $locations = $job->jobLocation->pluck('location')->filter()->unique()->join(', ');
                        $locations = $locations ?: ($job->location?->location ?: 'Location not set');
                        $alreadyMoved = $movedJobIds->contains((int) $job->id);
                    @endphp
                    <option value="{{ $job->id }}" {{ (int) old('job_id') === (int) $job->id ? 'selected' : '' }} {{ $alreadyMoved ? 'disabled' : '' }}>
                        {{ $job->title }} — {{ $job->company?->company_name ?? 'No company' }} — {{ $locations }}{{ $alreadyMoved ? ' (Already moved)' : '' }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-0 text-[12px] font-bold text-white shadow-sm hover:bg-blue-700">
                <i class="fa fa-arrow-right"></i> Move to Job
            </button>
        </form>
    </div>
</div>

<div class="rounded-2xl border border-[#E8E6E1] bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#8892A0]">Movement History</p>
            <h3 class="mt-1 text-[15px] font-bold text-[#1A1E2E]">Jobs this candidate was moved to</h3>
        </div>
        <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-600">{{ $jobMoves->count() }}</span>
    </div>
    <div class="divide-y divide-[#EEF0F3]">
        @forelse($jobMoves as $move)
            <div class="flex flex-col gap-3 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="truncate text-[13px] font-bold text-[#1A1E2E]">{{ $move->job?->title ?? 'Deleted job' }}</p>
                    <p class="mt-1 text-[11px] text-[#718096]">
                        {{ $move->job?->company?->company_name ?? 'Company not available' }}
                        · Moved by <strong>{{ $move->movedBy?->name ?? 'Unknown team member' }}</strong>
                        · {{ $move->created_at->timezone('America/Toronto')->format('d M Y, h:i A') }}
                    </p>
                </div>
                @if($move->job)
                    <a href="{{ route('admin.job-applications.table', ['jobs' => $move->job_id]) }}" class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-[11px] font-bold text-blue-600 hover:bg-blue-100">
                        View Job Applications <i class="fa fa-external-link"></i>
                    </a>
                @endif
            </div>
        @empty
            <div class="py-7 text-center text-[12px] text-[#9AA4B2]">This candidate has not been moved to any job yet.</div>
        @endforelse
    </div>
</div>
