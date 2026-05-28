<link rel="stylesheet" href="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}">
<link rel="stylesheet" href="{{ asset('assets/node_modules_files/html5-editor/bootstrap-wysihtml5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/node_modules_files/multiselect/css/multi-select.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/iCheck/all.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.css') }}">

<style>
    #updateSchedule {
        color: #1A1E2E;
    }

    #updateSchedule .int-card {
        border: 1px solid #ECE9E3;
        border-radius: 12px;
        padding: 14px 14px 2px;
        background: #fff;
        margin-bottom: 12px;
    }

    #updateSchedule .form-group > label,
    #updateSchedule .control-label,
    #updateSchedule .d-block {
        font-size: 11px;
        font-weight: 700;
        color: #6B7280;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    #updateSchedule .form-control,
    #updateSchedule .select2-container--default .select2-selection--single,
    #updateSchedule .select2-container--default .select2-selection--multiple {
        border-color: #D9E1EC;
        border-radius: 10px;
        min-height: 38px;
        box-shadow: none;
    }

    #updateSchedule .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }

    #updateSchedule .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    #updateSchedule .select2-container--default .select2-selection--multiple .select2-selection__choice {
        border: 0;
        border-radius: 999px;
        background: #EFF6FF;
        color: #1D4ED8;
        padding: 2px 9px;
        margin-top: 5px;
    }

    .online-radio-button {
        display: inline-flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .online-radio-button .int-type-option {
        margin: 0;
    }

    .online-radio-button .int-type-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .online-radio-button .int-type-option span {
        display: inline-flex;
        align-items: center;
        border: 1px solid #D9E1EC;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #4B5563;
        background: #F9FAFB;
        cursor: pointer;
    }

    .online-radio-button .int-type-option input:checked + span {
        border-color: #2563EB;
        color: #1D4ED8;
        background: #EFF6FF;
    }

    #updateSchedule .int-inline-radio {
        display: inline-flex;
        align-items: center;
        margin-right: 14px;
    }

    #updateSchedule .int-inline-radio input {
        margin-right: 6px;
    }

    #updateSchedule .int-reminder-wrap {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        color: #374151;
        text-transform: none !important;
        letter-spacing: 0 !important;
    }

</style>
    <form id="updateSchedule" class="ajax-form" method="put">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div id="alert"></div>
        <div class="form-body">
            <div class="row int-card">
                <div class="col-md-6  col-xs-12">
                    <div class="form-group">
                        <label class="d-block">@lang('modules.interviewSchedule.candidate')</label>
                        <select disabled class="select2 m-b-10 form-control"
                                data-placeholder="@lang('modules.interviewSchedule.chooseCandidate')">
                            @foreach($candidates as $candidate)
                                <option @if($schedule->job_application_id == $candidate->id) selected @endif value="{{ $candidate->id }}">{{ ucwords($candidate->full_name) }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="candidate_id" value="{{$schedule->job_application_id}}">
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div class="form-group">
                        <label class="d-block">@lang('modules.interviewSchedule.employee')</label>
                        <select class="select2 m-b-10 form-control select2-multiple " multiple="multiple"
                                data-placeholder="@lang('modules.interviewSchedule.chooseEmployee')" name="employee[]" id="employee">
                            @foreach($users as $emp)
                                <option  value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $user->id)
                                        (@lang('app.you')) @endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row int-card">
                <div class="col-xs-6 col-md-3">
                    <div class="form-group" >
                        <label>@lang('modules.interviewSchedule.scheduleDate')</label>
                        <input type="text" name="scheduleDate" id="scheduleDate" value="{{$schedule->schedule_date->format('Y-m-d')}}" class="form-control">
                    </div>
                </div>
                    
                <div class="col-xs-5 col-md-3">
                    <div class="form-group chooseCandidate bootstrap-timepicker timepicker" >
                        <label>@lang('modules.interviewSchedule.scheduleTime')</label>
                        <input type="text" name="scheduleTime" id="scheduleTime" value="{{$schedule->schedule_date->format('H:i')}}" class="form-control">
                    </div>
                </div>
                @if($zoom_setting->enable_zoom == 1)
                <div class="col-xs-5 col-md-3 ">
                    <div class="form-group" id="end_date_section">
                        <label>@lang('modules.interviewSchedule.endDate')</label>
                        <input type="text" name="end_date" id="end_date" value="{{ ($schedule->meeting != null) ?$schedule->meeting->end_date_time->format('Y-m-d') : '' }}" class="form-control">
                    </div>
                </div>

                <div class="col-xs-5 col-md-3">
                    <div class="form-group chooseCandidate bootstrap-timepicker timepicker" id="end_time_section">
                        <label>@lang('modules.interviewSchedule.endTime')</label>
                        <input type="text" name="end_time" id="end_time" value="{{  ($schedule->meeting != null)?$schedule->meeting->end_date_time->format('H:i') : ''}}" class="form-control">
                    </div>
                </div>
                <div class="col-xs-5 col-md-3">
                    <label>@lang('modules.interviewSchedule.interviewType')</label>
                    <div class="form-group online-radio-button">
                        <label class="int-type-option">
                            <input type="radio" name="interview_type" id="interview_typeOnline" value="online" {{ ($schedule->interview_type == 'online')? "checked" : "" }}>
                            <span>@lang('modules.meetings.online')</span>
                        </label>
                        <label class="int-type-option">
                            <input type="radio" name="interview_type" id="interview_typeOffline" value="offline" {{($schedule->interview_type == 'offline' || $schedule->interview_type == null)? "checked" : "" }}>
                            <span>@lang('modules.meetings.offline')</span>
                        </label>
                    </div>
                </div>
               @endif
            </div>
            @if($zoom_setting->enable_zoom == 1)
            <div class="row int-card" id="meeting-fields" style="display: none">

                <div class="col-xs-6 col-md-10">
                    <div class="form-group">
                        <label class="d-block">@lang('modules.interviewSchedule.interviewTitle')</label>
                        <input type="text" name="meeting_title" id="meeting_title" @if($schedule->meeting) value="{{$schedule->meeting->meeting_name ?? ''}}" @endif class="form-control">
                    </div>
                </div>
                
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <div class="m-b-10">
                            <label class="control-label">@lang('modules.zoommeeting.hostVideoStatus')</label>
                        </div>
                        <label class="int-inline-radio">
                            <input type="radio" name="host_video" id="host_video1" value="1" @if($schedule->meeting) {{ $schedule->meeting->host_video ? "checked" : "" }} @endif>
                            <span>@lang('app.enable')</span>
                        </label>
                        <label class="int-inline-radio">
                            <input type="radio" name="host_video" id="host_video2" value="0" @if($schedule->meeting) {{ !$schedule->meeting->host_video ? "checked" : "" }} @endif checked>
                            <span>@lang('app.disable')</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="m-b-10">
                            <label class="control-label">@lang('modules.zoommeeting.participantVideoStatus')</label>
                        </div>
                        <label class="int-inline-radio">
                            <input type="radio" name="participant_video" id="participant_video1" value="1" @if($schedule->meeting) {{ $schedule->meeting->participant_video ? "checked" : "" }} @endif>
                            <span>@lang('app.enable')</span>
                        </label>
                        <label class="int-inline-radio">
                            <input type="radio" name="participant_video" id="participant_video2" value="0" @if($schedule->meeting) {{ !$schedule->meeting->participant_video ? "checked" : "" }} @endif checked>
                            <span>@lang('app.disable')</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">@lang('modules.interviewSchedule.host')</label>
                        <select class="select2 form-control" id="created_by" name="created_by">
                            @foreach ($users as $emp)
                                <option @if ($emp->id == $user->id) selected @endif value="{{ $emp->id }}">{{ ucwords($emp->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label class="int-reminder-wrap">
                        <input type="checkbox" value="1" name="send_reminder" @if($schedule->meeting) {{ ($schedule->meeting->send_reminder)? "checked" : "" }} @endif id="send_reminder">
                        <span>@lang('modules.zoommeeting.reminder')</span>
                    </label>

                </div>
                <div class="col-md-12" id="reminder-fields" style="display: none;">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('modules.zoommeeting.remindBefore')</label>
                                <input type="number" min="1" @if($schedule->meeting)  value="{{$schedule->meeting->remind_time}}" @endif value="1" name="remind_time" class="form-control">
                            </div>
                        </div>
                        <div class="col-xs-6 col-md-3">
                            <div class="form-group repeat_type_dropdown">
                                <label>&nbsp;</label>
                                <select name="remind_type" id="" class="form-control">
                                    <option value="day" @if($schedule->meeting)  {{$schedule->meeting->remind_type == "day" ? 'selected' : ''}} @endif>@lang('modules.zoommeeting.day')</option>
                                    <option value="hour" @if($schedule->meeting)  {{$schedule->meeting->remind_type == "day" ? 'selected' : ''}} @endif>@lang('modules.zoommeeting.hour')</option>
                                    <option value="minute" @if($schedule->meeting)  {{$schedule->meeting->remind_type == "day" ? 'selected' : ''}} @endif>@lang('modules.zoommeeting.minute')</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="row int-card">
                <div class="col-xs-12 col-md-12 ">
                    <div class="form-group">
                        <label>@lang('modules.interviewSchedule.comment')</label>
                        <textarea type="text" name="comment" id="comment" class="form-control">{{ $comment->comment ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex flex-wrap items-center justify-end gap-2 border-t border-[#F0EEE9] pt-5">
            <button type="button" class="jc-btn-cancel" onclick="if (typeof window.closeInterviewScheduleEditModal === 'function') window.closeInterviewScheduleEditModal();">@lang('app.cancel')</button>
            <button type="button" class="jc-btn-save update-schedule">@lang('app.update')</button>
        </div>
    </form>
<script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/multiselect/js/jquery.multi-select.js') }}"></script>
<script src="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.js') }}"></script>

<script>
    
    var $editShell = $('#scheduleEditModal .modal-data-shell');
    var editDropdownParent = $editShell.length ? $editShell : $(document.body);
    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        },
        width: '100%',
        dropdownParent: editDropdownParent
    });

    $('#employee').val({{$employeeList}}).change();

    // Datepicker Set
    $('#scheduleDate').bootstrapMaterialDatePicker
    ({
        time: false,
        clearButton: true,
    });
    $('#end_date').bootstrapMaterialDatePicker
    ({
        time: false,
        clearButton: true,
        minDate : new Date()
    });
    $('#end_time').bootstrapMaterialDatePicker
        ({
            date: false,
            shortTime: true,   // look it
            format: 'HH:mm',
            switchOnClick: true
        });
    if ($('#colorselector').length) {
        $('#colorselector').colorselector();
    }
    $('#send_reminder').is(':checked') ? $('#reminder-fields').show() : $('#reminder-fields').hide();
    // $('#interview_type').is(':checked') ? $('#meeting-fields').show() : $('#meeting-fields').hide();
   
   
    // Timepicker Set
    $('#scheduleTime').bootstrapMaterialDatePicker
    ({
        date: false,
        shortTime: true,   // look it
        format: 'HH:mm',
        switchOnClick: true
    });

    // Update Schedule
    $('.update-schedule').click(function () {
        $.easyAjax({
            url: '{{route('admin.interview-schedule.update', $schedule->id)}}',
            container: '#updateSchedule',
            type: "PUT",
            data: $('#updateSchedule').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    })
    $('#send_reminder').on('change', function() {
        if ($(this).is(':checked')) {
            $('#reminder-fields').show();
        } else {
            $('#reminder-fields').hide();
        }
    });
        @if($schedule->interview_type == 'online')
        $('#end_time_section').show();
        $('#end_date_section').show();
        @else
        $('#end_date_section').hide();
        $('#end_time_section').hide();
        @endif
        var value = $('input[name="interview_type"]:checked').val();
        if(value == 'offline'){
            $('#meeting-fields').hide();
            $('#end_date_section').hide();
            $('#end_time_section').hide();
        }else{
            $('#meeting-fields').show();
            $('#end_date_section').show();
            $('#end_time_section').show();
            
        }

    $('input[type=radio][name=interview_type]').change(function () {
            if(this.value == 'online'){
            $('#meeting-fields').show();
            $('#end_date_section').show();
            $('#end_time_section').show();
        }
        else{
            $('#meeting-fields').hide();
            $('#end_date_section').hide();
            $('#end_time_section').hide();
        }
    })
</script>
