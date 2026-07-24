<div class="ats-status-tooltip text-left">
    <div class="rounded-xl border border-[#DCE2EB] bg-white p-3 shadow-[0_12px_32px_rgba(28,39,60,0.16)]">
        <div class="mb-2 flex items-center justify-between border-b border-[#EEF0F4] pb-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.06em] text-[#7D8796]">Applicant Status</span>
            <span class="text-[11px] text-[#A0A8B5]">{{ $statuses->count() }} stages</span>
        </div>
        <div class="max-h-[260px] space-y-1 overflow-y-auto">
            @forelse($statuses as $status)
                <div class="flex items-center justify-between gap-3 rounded-lg px-2 py-1.5 hover:bg-[#F7F9FC]">
                    <span class="flex min-w-0 items-center gap-2">
                        <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $status->color ?: '#6B7280' }}"></span>
                        <span class="truncate text-[12px] font-medium text-[#4D586B]">{{ ucwords(str_replace(['_', '-'], ' ', $status->status)) }}</span>
                    </span>
                    <strong class="min-w-[26px] rounded-md bg-[#F0F3F7] px-1.5 py-0.5 text-center text-[11.5px] text-[#1A1E2E]">{{ number_format($status->applicant_count) }}</strong>
                </div>
            @empty
                <p class="px-2 py-3 text-center text-[12px] text-[#8892A0]">No statuses configured.</p>
            @endforelse
        </div>
    </div>
</div>
