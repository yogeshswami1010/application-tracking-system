<link rel="stylesheet" href="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}">
<link rel="stylesheet" href="{{ asset('assets/node_modules_files/html5-editor/bootstrap-wysihtml5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/node_modules_files/multiselect/css/multi-select.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/iCheck/all.css') }}">
<style>
.online-radio-button{
        display: inline-flex;
    }
</style>
<div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
    <h4 class="text-lg font-semibold text-gray-900"><i class="icon-plus"></i> @lang('modules.interviewSchedule.interviewSchedule')</h4>
    <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors" onclick="$(this).closest('.modal').addClass('hidden')"><i class="fa fa-times"></i></button>
</div>
<div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
    <form id="createSchedule" class="ajax-form" method="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.interviewSchedule.candidate')</label>
                    <p class="text-gray-900">{{ $currentApplicant->full_name }}</p>
                    <input type="hidden" name="candidates[]" value="{{ $currentApplicant->id }}">
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.interviewSchedule.employee')</label>
                    <select class="select2 form-control select2-multiple" multiple="multiple"
                            data-placeholder="@lang('modules.interviewSchedule.chooseEmployee')" name="employees[]">
                        @foreach($users as $emp)
                            <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $user->id)
                                    (@lang('app.you')) @endif</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.interviewSchedule.scheduleDate')</label>
                    <input type="text" name="scheduleDate" id="scheduleDate" placeholder="@lang('modules.interviewSchedule.scheduleDate')" value="{{$scheduleDate}}" class="form-control">
                </div>

                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.interviewSchedule.scheduleTime')</label>
                    <input type="text" name="scheduleTime" id="scheduleTime" placeholder="@lang('modules.interviewSchedule.scheduleTime')" class="form-control">
                </div>
            @if($zoom_setting->enable_zoom == 1)
                <div class="form-group hidden" id="end_date_section">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.interviewSchedule.endDate')</label>
                    <input type="text" name="end_date" id="end_date" data-placeholder="@lang('modules.interviewSchedule.endDate')" value="{{$scheduleDate}}" class="form-control">
                </div>
            
                <div class="form-group hidden" id="end_time_section">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.interviewSchedule.scheduleTime')</label>
                    <input type="text" name="end_time" id="end_time" data-placeholder="@lang('modules.interviewSchedule.scheduleTime')" class="form-control">
                </div>
            @endif
            </div>
        @if($zoom_setting->enable_zoom == 1)
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-2">@lang('modules.interviewSchedule.interviewType')</label>
                <div class="flex items-center gap-6">
                    <div class="flex items-center">
                        <input type="radio" name="interview_type" id="interview_typeOnline" value="online" class="mr-2">
                        <label for="interview_typeOnline" class="text-sm text-gray-700">@lang('modules.meetings.online')</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="interview_type" id="interview_typeOffline" value="offline" checked class="mr-2">
                        <label for="interview_typeOffline" class="text-sm text-gray-700">@lang('modules.meetings.offline')</label>
                    </div>
                </div>
            </div>
            
            <div class="hidden space-y-4" id="repeat-fields">
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.interviewSchedule.interviewTitle')</label>
                    <input type="text" name="meeting_title" id="meeting_title" class="form-control">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">@lang('modules.zoommeeting.hostVideoStatus')</label>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center">
                                <input type="radio" name="host_video" id="host_video1" value="1" class="mr-2">
                                <label for="host_video1" class="text-sm text-gray-700">@lang('app.enable')</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="host_video" id="host_video2" value="0" checked class="mr-2">
                                <label for="host_video2" class="text-sm text-gray-700">@lang('app.disable')</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">@lang('modules.zoommeeting.participantVideoStatus')</label>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center">
                                <input type="radio" name="participant_video" id="participant_video1" value="1" class="mr-2">
                                <label for="participant_video1" class="text-sm text-gray-700">@lang('app.enable')</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="participant_video" id="participant_video2" value="0" checked class="mr-2">
                                <label for="participant_video2" class="text-sm text-gray-700">@lang('app.disable')</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.interviewSchedule.host')</label>
                        <select class="select2 form-control" id="created_by" name="created_by">
                            @foreach($users as $emp)
                                <option @if($emp->id == $user->id) selected @endif value="{{ $emp->id }}">{{ ucwords($emp->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="flex items-center">
                        <input type="checkbox" value="1" name="send_reminder" class="flat-red mr-2" id="send_reminder">
                        <span class="text-sm text-gray-700">@lang('modules.zoommeeting.reminder')</span>
                    </label>
                </div>
                <div class="hidden grid grid-cols-1 md:grid-cols-2 gap-4" id="reminder-fields">
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.zoommeeting.remindBefore')</label>
                        <input type="number" min="1" value="1" name="remind_time" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.zoommeeting.remindType')</label>
                        <select name="remind_type" class="form-control">
                            <option value="day">@lang('modules.zoommeeting.day')</option>
                            <option value="hour">@lang('modules.zoommeeting.hour')</option>
                            <option value="minute">@lang('modules.zoommeeting.minute')</option>
                        </select>
                    </div>
                </div>
            </div>
        @else
            <input type="hidden" name="interview_type" value="offline">
        @endif

        <div class="form-group">
            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.interviewSchedule.comment')</label>
            <textarea type="text" name="comment" id="comment" placeholder="@lang('modules.interviewSchedule.comment')" class="form-control" rows="4"></textarea>
        </div>
        </div>
    </form>

</div>
<div class="flex items-center justify-end space-x-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
    <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" onclick="$(this).closest('.modal').addClass('hidden')">@lang('app.close')</button>
    <button type="button" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 save-schedule">@lang('app.submit')</button>
</div>

<script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/multiselect/js/jquery.multi-select.js') }}"></script>
<script src="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.js') }}"></script>


<script>
    // Select 2 init.
    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        },
        width: '100%'
    });
    // Datepicker set
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
    // $('#colorselector').colorselector();

    // Timepicker Set
    $('#scheduleTime').bootstrapMaterialDatePicker
    ({
        date: false,
        shortTime: true,   // look it
        format: 'HH:mm',
        switchOnClick: true
    });
    
    $('#end_time').bootstrapMaterialDatePicker
        ({
            date: false,
            shortTime: true,   // look it
            format: 'HH:mm',
            switchOnClick: true
        });
        $('#send_reminder').on('ifChecked', function(event){
            $('#reminder-fields').removeClass('hidden');
        });
        
        $('#send_reminder').on('ifUnchecked', function(event){
            $('#reminder-fields').addClass('hidden');
        });
        
        $('#send_reminder').iCheck({
            checkboxClass: 'icheckbox_flat-blue',
        })
        
        $('input[type=radio][name=interview_type]').change(function () {
            if(this.value == 'online'){
                $('#repeat-fields').removeClass('hidden');
                $('#end_time_section').removeClass('hidden');
                $('#end_date_section').removeClass('hidden');
            }
            else{
                $('#repeat-fields').addClass('hidden');
                $('#end_time_section').addClass('hidden');
                $('#end_date_section').addClass('hidden');
            }
        })
</script>
