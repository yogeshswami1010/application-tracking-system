<div class="ats-status-tooltip text-left">
    <div class="rounded-xl border border-[#DCE2EB] bg-white p-3 shadow-[0_12px_32px_rgba(28,39,60,0.16)]">
        <div class="mb-2 flex items-center justify-between border-b border-[#EEF0F4] pb-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.06em] text-[#7D8796]">Applicant Status</span>
            <span class="text-[11px] text-[#A0A8B5]">{{ $statuses->count() }} stages</span>
        </div>
        <div class="max-h-[260px] space-y-1 overflow-y-auto">
            @forelse($statuses as $status)
                <div class="ats-status-stage rounded-lg px-2 py-1.5 hover:bg-[#F7F9FC]">
                    <div class="flex items-center justify-between gap-3">
                        <span class="flex min-w-0 items-center gap-2">
                            <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $status->color ?: '#6B7280' }}"></span>
                            <span class="truncate text-[12px] font-medium text-[#4D586B]">{{ ucwords(str_replace(['_', '-'], ' ', $status->status)) }}</span>
                        </span>
                        <strong class="min-w-[26px] rounded-md bg-[#F0F3F7] px-1.5 py-0.5 text-center text-[11.5px] text-[#1A1E2E]">{{ number_format($status->applicant_count) }}</strong>
                    </div>
                    <div class="ats-status-applicants">
                        <div class="sticky top-0 z-10 flex items-center justify-between border-b border-[#E6EAF0] bg-white px-3 py-2.5">
                            <span class="truncate text-[11px] font-bold uppercase tracking-[0.05em] text-[#5A6478]">{{ ucwords(str_replace(['_', '-'], ' ', $status->status)) }}</span>
                            <span class="ml-2 rounded-md bg-[#EEF4FF] px-2 py-0.5 text-[11px] font-bold text-[#2563EB]">{{ number_format($status->applicant_count) }}</span>
                        </div>
                        <div class="px-2 py-1">
                        @forelse($status->applicants as $applicant)
                            <button type="button"
                                    class="ats-open-applicant flex w-full items-center gap-2 border-b border-[#EEF0F4] px-1 py-2 text-left transition-colors last:border-b-0 hover:bg-[#F3F7FF]"
                                    data-applicant-id="{{ $applicant->id }}"
                                    title="Open {{ $applicant->full_name }} profile">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#E8F0FF] text-[9px] font-bold text-[#2563EB]">{{ strtoupper(substr(trim($applicant->full_name), 0, 1)) }}</span>
                                <span class="truncate text-[11.5px] font-semibold text-[#3D4A5C] hover:text-[#2563EB]">{{ $applicant->full_name }}</span>
                                <i class="fa fa-angle-right ml-auto text-[11px] text-[#A0A8B5]"></i>
                            </button>
                        @empty
                            <div class="px-2 py-4 text-center text-[11px] text-[#A0A8B5]">No applicants in this status.</div>
                        @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <p class="px-2 py-3 text-center text-[12px] text-[#8892A0]">No statuses configured.</p>
            @endforelse
        </div>
    </div>
</div>
