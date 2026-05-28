<style>
    #schedule-show-root { color: #1A1E2E; }
    #schedule-show-root h4 { margin: 0; font-size: 16px; font-weight: 700; color: #111827; }
    #schedule-show-root h5 { margin: 0; font-size: 14px; font-weight: 700; color: #111827; }
    #schedule-show-root p { margin: 0; line-height: 1.45; }
    #schedule-show-root .text-muted { color: #4B5563 !important; }
    #schedule-show-root .int-card { border: 1px solid #ECE9E3; border-radius: 12px; padding: 14px; background: #fff; margin-bottom: 12px; }
    #schedule-show-root .int-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
    #schedule-show-root .int-grid { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 10px 14px; }
    #schedule-show-root .int-col-4 { grid-column: span 4 / span 4; }
    #schedule-show-root .int-col-6 { grid-column: span 6 / span 6; }
    #schedule-show-root .int-col-12 { grid-column: span 12 / span 12; }
    #schedule-show-root .int-label { display: block; margin-bottom: 2px; font-size: 11px; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: .04em; }
    #schedule-show-root .int-value { color: #111827; font-size: 14px; word-break: break-word; }
    #schedule-show-root .notify-button-show { display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 11px; border-radius: 8px; font-size: 12px !important; font-weight: 600; line-height: 1 !important; }
    #schedule-show-root .badge { border-radius: 999px; padding: 4px 10px; font-size: 11px; font-weight: 600; }
    #schedule-show-root .int-employee-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 10px; border: 1px solid #EEF2F7; border-radius: 10px; background: #FCFDFE; margin-bottom: 8px; }
    #schedule-show-root .int-actions { display: inline-flex; flex-wrap: wrap; gap: 8px; }
    #schedule-show-root .int-status-group { display: flex; flex-wrap: wrap; gap: 8px; }
    #schedule-show-root .int-status-option { position: relative; margin: 0; }
    #schedule-show-root .int-status-option > input { position: absolute; opacity: 0; pointer-events: none; }
    #schedule-show-root .int-status-option > span { display: inline-flex; align-items: center; padding: 7px 12px; border: 1px solid #D9E1EC; border-radius: 999px; font-size: 12px; font-weight: 600; color: #4B5563; background: #F9FAFB; cursor: pointer; transition: all .15s ease; }
    #schedule-show-root .int-status-option > input:checked + span { border-color: #2563EB; color: #1D4ED8; background: #EFF6FF; }
    @media (max-width: 768px) { #schedule-show-root .int-col-4, #schedule-show-root .int-col-6 { grid-column: span 12 / span 12; } }
</style>

<div id="schedule-show-root">
    <div class="mb-4 flex flex-wrap items-center gap-2">
        @if ($schedule->interview_type == 'online')
            <span class="inline-flex rounded-full bg-[#EFF6FF] px-2.5 py-1 text-[11px] font-semibold text-[#2563EB]">@lang('app.online')</span>
        @else
            <span class="inline-flex rounded-full bg-[#F1F3F7] px-2.5 py-1 text-[11px] font-semibold text-[#5A6478]">@lang('app.offline')</span>
        @endif
    </div>

    <div class="int-card">
        <div class="int-head"><h4>@lang('modules.interviewSchedule.candidateDetail')</h4></div>
        <div class="int-grid">
            <div class="int-col-4">
                <span class="int-label">@lang('app.name')</span>
                <p class="int-value">{{ ucwords($schedule->jobApplication->full_name) }}</p>
            </div>
            <div class="int-col-4">
                <span class="int-label">@lang('app.email')</span>
                <p class="int-value">{{ $schedule->jobApplication->email }}</p>
            </div>
            <div class="int-col-4">
                <span class="int-label">@lang('app.phone')</span>
                <p class="int-value">{{ $schedule->jobApplication->phone }}</p>
            </div>
        </div>
    </div>

    <div class="int-card">
        <div class="int-head">
            <h5>@lang('modules.interviewSchedule.scheduleEditDetail')</h5>
            @if ($currentDateTimestamp <= $schedule->schedule_date->timestamp && $user->cans('edit_schedule'))
                <button onclick="editSchedule()" class="btn btn-sm btn-info notify-button-show" title="Edit"><i class="fa fa-pencil"></i> @lang('app.edit')</button>
            @endif
        </div>
        <div class="int-grid">
            <div class="int-col-12">
                <span class="int-label">@lang('modules.interviewSchedule.job')</span>
                <p class="int-value">{{ ucwords($schedule->jobApplication->job->title) . ' (' . ucwords($schedule->jobApplication->job->location->location) . ')' }}</p>
            </div>
            <div class="int-col-6">
                <span class="int-label">@lang('modules.interviewSchedule.interviewType')</span>
                <p class="int-value">{{ $schedule->interview_type == 'online' ? __('app.online') : __('app.offline') }}</p>
            </div>
            @if ($schedule->interview_type == 'online' && $schedule->meeting->status != 'meeting' && $schedule->meeting->status != 'canceled')
                <div class="int-col-6">
                    <span class="int-label">@lang('app.actions')</span>
                    <div class="int-actions">
                        @if ($schedule->jobApplication->resume_url)
                            <a target="_blank" href="{{ $schedule->jobApplication->resume_url }}" class="btn btn-sm btn-primary">@lang('app.view') @lang('modules.jobApplication.resume')</a>
                        @endif
                        @php
                            $event = $schedule->meeting;
                            if ($zoom_setting->meeting_app == 'in_app') {
                                $url = route('admin.zoom-meeting.startMeeting', $event->id);
                            } else {
                                $url = user()->id == $event->created_by ? $event->start_link : $event->end_link;
                            }
                        @endphp
                        @if (user()->id == $event->created_by)
                            @if ($event->status == 'waiting')
                                @php $meetingDate = $event->start_date_time->toDateString(); @endphp
                                @if (is_null($event->occurrence_id) || $nowDate == $meetingDate)
                                    <a href="{{ $url }}" target="_blank" class="btn btn-success btn-sm waves-effect"><i class="fa fa-play"></i> @lang('modules.zoommeeting.startMeeting')</a>
                                @endif
                            @endif
                        @else
                            @if ($event->status == 'waiting' || $event->status == 'live')
                                @php $meetingDate = $event->start_date_time->toDateString(); @endphp
                                @if (is_null($event->occurrence_id) || $nowDate == $meetingDate)
                                    <a href="{{ $url }}" target="_blank" class="btn btn-info btn-sm waves-effect"><i class="fa fa-play"></i> @lang('modules.zoommeeting.joinUrl')</a>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="int-card">
        <div class="int-head"><h5>@lang('modules.interviewSchedule.assignedEmployee')</h5></div>
        @forelse($schedule->employee as $emp)
            <div class="int-employee-row">
                <span class="int-value">{{ ucwords($emp->user->name) }}</span>
                @if ($emp->user_accept_status == 'accept')
                    <span class="badge badge-success">{{ ucwords($emp->user_accept_status) }}</span>
                @elseif($emp->user_accept_status == 'refuse')
                    <span class="badge badge-danger">{{ ucwords($emp->user_accept_status) }}</span>
                @else
                    <span class="badge badge-warning">{{ ucwords($emp->user_accept_status) }}</span>
                @endif
            </div>
        @empty
            <p class="text-muted">@lang('modules.interviewSchedule.noEmployeeAssigned')</p>
        @endforelse
    </div>

    @if ($zoom_setting->enable_zoom == 1 && $schedule->interview_type == 'online')
        <div class="int-card">
            <div class="int-head"><h5>@lang('app.zoomMeeting')</h5></div>
            <div class="int-grid">
                <div class="int-col-6"><span class="int-label">@lang('modules.zoommeeting.meetingName')</span><p class="int-value">{{ ucwords($schedule->meeting->meeting_name) }}</p></div>
                <div class="int-col-6">
                    <span class="int-label">@lang('modules.zoommeeting.meetingStatus')</span>
                    <p class="int-value">
                        @if ($schedule->meeting->status == 'waiting')
                            <span class="label label-warning badge">{{ __('modules.zoommeeting.waiting') }}</span>
                        @elseif ($schedule->meeting->status == 'live')
                            <i class="fa fa-circle Blink" style="color: red"></i> <span class="font-semi-bold">{{ __('modules.zoommeeting.live') }}</span>
                        @elseif ($schedule->meeting->status == 'canceled')
                            <span class="label label-danger badge">{{ __('app.canceled') }}</span>
                        @elseif ($schedule->meeting->status == 'finished')
                            <span class="label label-success badge">{{ __('app.finished') }}</span>
                        @endif
                    </p>
                </div>
                <div class="int-col-6"><span class="int-label">@lang('modules.zoommeeting.startOn')</span><p class="int-value">{{ $schedule->meeting->start_date_time }}</p></div>
                <div class="int-col-6"><span class="int-label">@lang('modules.zoommeeting.endOn')</span><p class="int-value">{{ $schedule->meeting->end_date_time }}</p></div>
                <div class="int-col-6"><span class="int-label">@lang('app.zoomMeeting') @lang('app.password')</span><p class="int-value">{{ $schedule->meeting->password ?? '--' }}</p></div>
                <div class="int-col-6"><span class="int-label">@lang('modules.zoommeeting.hostVideoStatus')</span><p class="int-value">{{ $schedule->meeting->host_video ? __('modules.meetings.enabled') : __('modules.meetings.disabled') }}</p></div>
            </div>
        </div>
    @endif

    @if ($schedule->jobApplication->schedule->comments == 'interview' && count($application->schedule->comments) > 0)
        <div class="int-card">
            <div class="int-head"><h5>@lang('modules.interviewSchedule.comments')</h5></div>
            @forelse($schedule->jobApplication->schedule->comments as $comment)
                <div class="int-employee-row">
                    <div>
                        <p class="int-value">{{ $comment->user->name }}</p>
                        <p class="text-muted">{{ $comment->comment }}</p>
                    </div>
                </div>
            @empty
            @endforelse
        </div>
    @endif

    <div class="int-card">
        <label class="int-label mb-2" style="text-transform:none; letter-spacing:0;">@lang('app.status')</label>
        <div class="int-status-group">
            <label for="rejected" class="int-status-option"><input id="rejected" type="radio" @if ($schedule->status == 'rejected') checked @endif name="r1" class="minimal"><span>@lang('app.rejected')</span></label>
            <label for="hired" class="int-status-option"><input id="hired" type="radio" @if ($schedule->status == 'hired') checked @endif name="r1" class="minimal"><span>@lang('app.hired')</span></label>
            <label for="pending" class="int-status-option"><input id="pending" type="radio" @if ($schedule->status == 'pending') checked @endif name="r1" class="minimal"><span>@lang('app.pending')</span></label>
            <label for="canceled" class="int-status-option"><input id="canceled" type="radio" @if ($schedule->status == 'canceled') checked @endif name="r1" class="minimal"><span>@lang('app.canceled')</span></label>
        </div>
    </div>
</div>

<div class="mt-6 flex justify-end border-t border-[#F0EEE9] pt-4">
    <button type="button" class="jc-btn-cancel" onclick="if (typeof window.closeInterviewScheduleDetailModal === 'function') window.closeInterviewScheduleDetailModal();">@lang('app.close')</button>
</div>

<script>
    var statusChangeLock = false;
    var handleStatusSelection = function (status) {
        if (statusChangeLock) {
            return;
        }
        statusChangeLock = true;
        statusChange(status);
        setTimeout(function () {
            statusChangeLock = false;
        }, 250);
    };

    $('input[type="radio"].minimal').off('change').on('change', function() {
        handleStatusSelection($(this).prop('id'));
    });

    function statusChange(status) {
        var msg;
        swal({
            title: "@lang('errors.askForCandidateEmail')",
            text: msg,
            type: "info",
            showCancelButton: true,
            confirmButtonColor: "#0c19dd",
            confirmButtonText: "@lang('app.yes')",
            cancelButtonText: "@lang('app.no')",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm) {
            if (isConfirm) {
                statusChangeConfirm(status, 'yes')
            } else {
                statusChangeConfirm(status, 'no')
            }
        });
    }

    function statusChangeConfirm(status, mailToCandidate) {
        var token = "{{ csrf_token() }}";
        var id = "{{ $schedule->id }}";
        $.easyAjax({
            url: '{{ route('admin.interview-schedule.change-status') }}',
            container: '#schedule-show-root',
            type: "POST",
            data: {
                '_token': token,
                'status': status,
                'id': id,
                'mailToCandidate': mailToCandidate
            },
            success: function(response) {
                @if ($tableData)
                    table.draw(false);
                @else
                    reloadSchedule();
                @endif
                if (typeof window.closeInterviewScheduleDetailModal === 'function') {
                    window.closeInterviewScheduleDetailModal();
                }
            }
        })
    }

    function editSchedule() {
        var url = "{{ route('admin.interview-schedule.edit', $schedule->id) }}";
        if (typeof window.closeInterviewScheduleDetailModal === 'function') {
            window.closeInterviewScheduleDetailModal();
        }
        if (typeof intEditTitle !== 'undefined') {
            $('#scheduleEditModalHeading').text(intEditTitle);
        } else {
            $('#scheduleEditModalHeading').text(@json(__('app.edit')));
        }
        $.ajaxModal('#scheduleEditModal', url);
    }
</script>
