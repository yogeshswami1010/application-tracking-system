<link rel="stylesheet" href="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}">
<style>
    .is-int-dtp { z-index: 10060 !important; }
    .dtp { z-index: 10060 !important; }
    .select2-container { width: 100% !important; }
    .select2-container .select2-selection--single,
    .select2-container .select2-selection--multiple {
        border: 1.5px solid #e2ded8 !important;
        border-radius: 10px !important;
        min-height: 44px !important;
        background: #fff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1a1e2e !important;
        line-height: 42px !important;
        padding-left: 14px !important;
        padding-right: 34px !important;
        font-size: 13.5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
        right: 8px !important;
    }
    .select2-container--default .select2-selection--multiple {
        padding: 5px 10px !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
        padding: 0 !important;
    }
    .select2-container--default .select2-search--inline .select2-search__field {
        margin: 0 !important;
        height: 30px !important;
        font-size: 13.5px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
    }
    .select2-dropdown { z-index: 10070 !important; }
</style>

<form id="createSchedule" class="ajax-form" method="post">
    @csrf
    <div id="alert"></div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div class="form-group">
            <label for="int-create-candidates" class="jc-field-label">@lang('modules.interviewSchedule.candidate') <span class="text-red-400">*</span></label>
            <select class="select2 jc-field-input !h-auto !py-2"
                    id="int-create-candidates"
                    data-placeholder="@lang('modules.interviewSchedule.chooseCandidate')"
                    name="candidates">
                <option value="">@lang('modules.interviewSchedule.chooseCandidate')</option>
                @foreach($candidates as $candidate)
                    <option value="{{ $candidate->id }}">{{ ucwords($candidate->full_name) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="int-create-employees" class="jc-field-label">@lang('modules.interviewSchedule.employee') <span class="text-red-400">*</span></label>
            <select class="select2 select2-multiple jc-field-input !h-auto !min-h-[42px] !py-2"
                    id="int-create-employees"
                    multiple="multiple"
                    data-placeholder="@lang('modules.interviewSchedule.chooseEmployee')"
                    name="employees[]">
                @foreach($users as $emp)
                    <option value="{{ $emp->id }}">{{ ucwords($emp->name) }}@if($emp->id == $user->id) (@lang('app.you'))@endif</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="form-group">
            <label for="scheduleDate" class="jc-field-label">@lang('modules.interviewSchedule.scheduleDate') <span class="text-red-400">*</span></label>
            <input type="text" name="scheduleDate" id="scheduleDate" value="{{ $scheduleDate }}" class="jc-field-input" autocomplete="off">
        </div>
        <div class="form-group">
            <label for="scheduleTime" class="jc-field-label">@lang('modules.interviewSchedule.scheduleTime') <span class="text-red-400">*</span></label>
            <input type="text" name="scheduleTime" id="scheduleTime" class="jc-field-input" autocomplete="off">
        </div>
        @if($zoom_setting->enable_zoom == 1)
            <div class="form-group" id="end_date_section" style="display: none">
                <label for="end_date" class="jc-field-label">@lang('modules.interviewSchedule.endDate') <span class="text-red-400">*</span></label>
                <input type="text" name="end_date" id="end_date" value="{{ $scheduleDate }}" class="jc-field-input" autocomplete="off">
            </div>
            <div class="form-group" id="end_time_section" style="display: none">
                <label for="end_time" class="jc-field-label">@lang('modules.interviewSchedule.endTime') <span class="text-red-400">*</span></label>
                <input type="text" name="end_time" id="end_time" class="jc-field-input" autocomplete="off">
            </div>
        @endif
    </div>

    @if($zoom_setting->enable_zoom == 1)
        <div class="mt-5 form-group">
            <p class="jc-field-label mb-2">@lang('modules.interviewSchedule.interviewType')</p>
            <div class="flex flex-wrap items-center gap-6">
                <label class="inline-flex cursor-pointer items-center gap-2 text-[13.5px] text-[#3D4A5C]">
                    <input type="radio" name="interview_type" id="interview_typeOnline" value="online" class="h-4 w-4 border-[#E2DED8] text-[#2563EB] focus:ring-[#2563EB]">
                    <span>@lang('modules.meetings.online')</span>
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2 text-[13.5px] text-[#3D4A5C]">
                    <input type="radio" name="interview_type" id="interview_type_offline" value="offline" checked class="h-4 w-4 border-[#E2DED8] text-[#2563EB] focus:ring-[#2563EB]">
                    <span>@lang('modules.meetings.offline')</span>
                </label>
            </div>
        </div>

        <div id="repeat-fields" class="mt-5 space-y-5" style="display: none">
            <div class="form-group">
                <label for="meeting_title" class="jc-field-label">@lang('modules.interviewSchedule.interviewTitle')</label>
                <input type="text" name="meeting_title" id="meeting_title" class="jc-field-input" autocomplete="off">
            </div>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div class="form-group">
                    <p class="jc-field-label mb-2">@lang('modules.zoommeeting.hostVideoStatus')</p>
                    <div class="flex flex-wrap gap-4 text-[13px] text-[#3D4A5C]">
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="radio" name="host_video" id="host_video1" value="1" class="h-4 w-4 border-[#E2DED8] text-[#2563EB]">
                            <span>@lang('app.enable')</span>
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="radio" name="host_video" id="host_video2" value="0" checked class="h-4 w-4 border-[#E2DED8] text-[#2563EB]">
                            <span>@lang('app.disable')</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <p class="jc-field-label mb-2">@lang('modules.zoommeeting.participantVideoStatus')</p>
                    <div class="flex flex-wrap gap-4 text-[13px] text-[#3D4A5C]">
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="radio" name="participant_video" id="participant_video1" value="1" class="h-4 w-4 border-[#E2DED8] text-[#2563EB]">
                            <span>@lang('app.enable')</span>
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="radio" name="participant_video" id="participant_video2" value="0" checked class="h-4 w-4 border-[#E2DED8] text-[#2563EB]">
                            <span>@lang('app.disable')</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="created_by" class="jc-field-label">@lang('modules.interviewSchedule.host')</label>
                    <select class="select2 jc-field-input !h-auto !py-2" id="created_by" name="created_by">
                        @foreach($users as $emp)
                            <option value="{{ $emp->id }}" @if($emp->id == $user->id) selected @endif>{{ ucwords($emp->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="inline-flex cursor-pointer items-center gap-2 text-[13.5px] text-[#3D4A5C]">
                    <input type="checkbox" value="1" name="send_reminder" id="send_reminder" class="h-4 w-4 rounded border-[#E2DED8] text-[#2563EB] focus:ring-[#2563EB]">
                    <span>@lang('modules.zoommeeting.reminder')</span>
                </label>
            </div>
            <div id="reminder-fields" class="grid grid-cols-1 gap-4 sm:grid-cols-2" style="display: none;">
                <div class="form-group">
                    <label for="remind_time" class="jc-field-label">@lang('modules.zoommeeting.remindBefore')</label>
                    <input type="number" min="1" value="1" name="remind_time" id="remind_time" class="jc-field-input">
                </div>
                <div class="form-group">
                    <label for="remind_type" class="jc-field-label">&nbsp;</label>
                    <select name="remind_type" id="remind_type" class="jc-field-input cursor-pointer">
                        <option value="day">@lang('modules.zoommeeting.day')</option>
                        <option value="hour">@lang('modules.zoommeeting.hour')</option>
                        <option value="minute">@lang('modules.zoommeeting.minute')</option>
                    </select>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-5 form-group">
        <label for="comment" class="jc-field-label">@lang('modules.interviewSchedule.comment')</label>
        <textarea name="comment" id="comment" rows="3" placeholder="@lang('modules.interviewSchedule.comment')" class="jc-field-input min-h-[88px] resize-y"></textarea>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-end gap-2 border-t border-[#F0EEE9] pt-5">
        <button type="button" class="jc-btn-cancel" onclick="if (typeof window.closeInterviewScheduleDetailModal === 'function') window.closeInterviewScheduleDetailModal();">@lang('app.cancel')</button>
        <button type="button" class="jc-btn-save save-schedule">@lang('app.submit')</button>
    </div>
</form>

<script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}" type="text/javascript"></script>
<script>
    (function () {
        var $modalRoot = $('#scheduleDetailModal');
        var $shell = $modalRoot.find('.modal-data-shell');
        var dropdownParent = $shell.length ? $shell : $(document.body);

        $('.select2').select2({
            formatNoMatches: function () {
                return "{{ __('messages.noRecordFound') }}";
            },
            width: '100%',
            dropdownParent: dropdownParent
        });

        $('#scheduleDate').bootstrapMaterialDatePicker({
            time: false,
            clearButton: true,
            minDate: new Date()
        });
        $('#end_date').bootstrapMaterialDatePicker({
            time: false,
            clearButton: true,
            minDate: new Date()
        });
        $('#scheduleTime').bootstrapMaterialDatePicker({
            date: false,
            shortTime: true,
            format: 'HH:mm',
            switchOnClick: true
        });
        $('#end_time').bootstrapMaterialDatePicker({
            date: false,
            shortTime: true,
            format: 'HH:mm',
            switchOnClick: true
        });

        $('.save-schedule').off('click').on('click', function () {
            $.easyAjax({
                url: '{{ route('admin.interview-schedule.store') }}',
                container: '#createSchedule',
                type: 'POST',
                data: $('#createSchedule').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        window.location.reload();
                    }
                }
            });
        });

        $('#send_reminder').on('change', function () {
            if ($(this).is(':checked')) {
                $('#reminder-fields').show();
            } else {
                $('#reminder-fields').hide();
            }
        });

        $('input[type=radio][name=interview_type]').off('change').on('change', function () {
            if (this.value === 'online') {
                $('#repeat-fields').show();
                $('#end_time_section').show();
                $('#end_date_section').show();
            } else {
                $('#repeat-fields').hide();
                $('#end_date_section').hide();
                $('#end_time_section').hide();
            }
        });
    })();
</script>
