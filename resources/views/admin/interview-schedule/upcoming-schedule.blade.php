@php
    $intBarColors = ['#2563EB', '#059669', '#7C3AED', '#F59E0B', '#F43F5E'];
@endphp
@forelse($upComingSchedules as $key => $upComingSchedule)
    @php
        $date = \Carbon\Carbon::createFromFormat('Y-m-d', $key);
    @endphp
    <div class="border-b border-[#F0EEE9] px-4 py-2">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-[#B0B8C4]">{{ $date->format('M d, Y') }}</p>
    </div>
    @forelse($upComingSchedule as $key => $dtData)
        @php
            $bar = $intBarColors[$dtData->id % count($intBarColors)];
        @endphp
        <div id="schedule-{{ $dtData->id }}"
             class="int-upcoming-item flex cursor-pointer gap-2.5 border-b border-[#F6F5F2] px-[18px] py-3 transition-colors last:border-b-0 hover:bg-[#FAFBFD]"
             onclick="getScheduleDetail(event, {{ $dtData->id }})">
            <div class="int-up-bar w-[3px] shrink-0 self-stretch rounded-sm" style="background:{{ $bar }};"></div>
            <div class="min-w-0 flex-1">
                <p class="text-[10.5px] font-bold text-[#B0B8C4]">{{ $dtData->schedule_date->format('h:i a') }}</p>
                <p class="mt-0.5 text-[12.5px] font-bold text-[#1A1E2E]">{{ ucfirst($dtData->jobApplication->job->title) }}</p>
                <p class="mt-0.5 text-[11.5px] text-[#8892A0]">{{ ucfirst($dtData->jobApplication->full_name) }}</p>
            </div>
            <div class="flex shrink-0 items-start gap-1" onclick="event.stopPropagation();">
                @if($user->cans('edit_schedule'))
                    <button type="button"
                            onclick="editUpcomingSchedule(event, '{{ $dtData->id }}')"
                            class="jc-action-btn jc-btn-edit editSchedule"
                            title="@lang('app.edit')">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="#2563EB" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                @endif
                @if($user->cans('delete_schedule'))
                    <button type="button"
                            data-schedule-id="{{ $dtData->id }}"
                            class="jc-action-btn jc-btn-del deleteSchedule"
                            title="@lang('app.delete')">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="#EF4444" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        @if(in_array($user->id, $dtData->employee->pluck('user_id')->toArray()))
            @php
                $empData = $dtData->employeeData($user->id);
            @endphp
            <div class="flex flex-wrap items-center justify-end gap-2 border-b border-[#F6F5F2] bg-[#FAFBFD] px-[18px] py-2" onclick="event.stopPropagation();">
                @if($empData->user_accept_status == 'accept')
                    <span class="inline-flex rounded-full bg-[#ECFDF5] px-2.5 py-1 text-[11px] font-semibold text-[#059669]">@lang('app.accepted')</span>
                @elseif($empData->user_accept_status == 'refuse')
                    <span class="inline-flex rounded-full bg-[#FFF1F2] px-2.5 py-1 text-[11px] font-semibold text-[#EF4444]">@lang('app.refused')</span>
                @else
                    <button type="button" onclick="employeeResponse({{ $empData->id }}, 'accept')"
                            class="rounded-lg bg-[#059669] px-3 py-1.5 text-[11.5px] font-semibold text-white transition hover:bg-[#047857] responseButton">
                        @lang('app.accept')
                    </button>
                    <button type="button" onclick="employeeResponse({{ $empData->id }}, 'refuse')"
                            class="rounded-lg border border-[#FECACA] bg-[#FFF1F2] px-3 py-1.5 text-[11.5px] font-semibold text-[#DC2626] transition hover:bg-[#FFE4E6] responseButton">
                        @lang('app.refuse')
                    </button>
                @endif
            </div>
        @endif
    @empty
    @endforelse
@empty
    <div class="px-4 py-10 text-center">
        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-2xl bg-[#F1F3F7]">
            <svg class="h-5 w-5" fill="none" stroke="#C4CBD4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-[12.5px] text-[#B0B8C4]">@lang('messages.noUpcomingScheduleFund')</p>
    </div>
@endforelse
