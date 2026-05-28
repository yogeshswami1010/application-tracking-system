<link rel="stylesheet" href="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}">
<link rel="stylesheet" href="{{ asset('assets/node_modules_files/html5-editor/bootstrap-wysihtml5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/node_modules_files/multiselect/css/multi-select.css') }}">
<style>
     .repeat_type_dropdown select{
        height: 32px !important;

    }
    </style>
<div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
    <h4 class="text-lg font-semibold text-gray-800"><i class="icon-plus mr-2"></i> @lang('modules.zoommeeting.editMeeting')</h4>
        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-gray-700" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
    
</div>
<div class="px-6 py-5">
    <form id="editMeeting" class="ajax-form" method="POST">
    @csrf
                @method('PUT')
    <input type="hidden" name ="id_field" id ="id_field"  value="{{$event->id}}" >
    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
        <div class="md:col-span-6">
            <div class="space-y-1">
                <label class="required block text-sm font-medium text-gray-700">@lang('modules.zoommeeting.meetingName')</label>
                <input type="text" name="meeting_title" id="meeting_title" value="{{$event->meeting_name}}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>
        <div class="md:col-span-4">
            <div class="space-y-1">
                    <label class="control-label flex items-center gap-2 text-sm font-medium text-gray-700">@lang('app.category')
                        <a href="javascript:;" id="add_category" class="inline-flex h-6 w-6 items-center justify-center rounded border border-emerald-500 text-xs text-emerald-600 transition hover:bg-emerald-50"><i class="fa fa-plus"></i></a>
                    </label>
                        <select class="select2 w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" id="category_id_edit" name="category_id">
                        <option value="">@lang('modules.message.pleaseSelectCategory')</option>
                            @foreach($categories as $category)
                                <option @if($category->id == $event->category_id)
                                    selected
                                    @endif value="{{ $category->id }}">{{ ucwords($category->category_name) }}</option>
                            @endforeach
                        </select>
            </div>
        </div>
    </div>
    
    <div class="mt-4 grid grid-cols-1 gap-4">
        <div class="col-span-1">
           
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">@lang('modules.zoommeeting.description')</label>
                <textarea name="description"  id="description" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ ucfirst($event->description) }}</textarea>
            </div>
        </div>
    </div>
   
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-12">
        <div class="md:col-span-3">
            <div class="space-y-1">
                <label class="required block text-sm font-medium text-gray-700">@lang('modules.zoommeeting.startOn')</label>
                <input type="text" name="start_date" id="start_date" value="{{ $event->start_date_time->format('Y-m-d') }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" autocomplete="none">
            </div>
        </div>
        <div class="md:col-span-3">
            <div class="bootstrap-timepicker timepicker space-y-1">
                <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                <input type="text" name="start_time"  value="{{ $event->start_date_time->format('H:i') }}"  id="start_time" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>

        <div class="md:col-span-3">
            <div class="space-y-1">
                <label class="required block text-sm font-medium text-gray-700">@lang('modules.zoommeeting.endOn')</label>
                <input type="text" name="end_date" id="end_date" value="{{ $event->end_date_time->format('Y-m-d')  }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" autocomplete="none">
            </div>
        </div>
        <div class="md:col-span-3">
            <div class="bootstrap-timepicker timepicker space-y-1">
                <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                <input type="text" name="end_time" id="end_time" value="{{ $event->end_date_time->format('H:i')  }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>
    </div>
    <div class="mt-4 grid grid-cols-1 gap-4">
        <div class="col-span-1">
            <div class="space-y-1">
                <label class="control-label block text-sm font-medium text-gray-700">@lang('modules.meetings.addEmployees')
                     </label>
                     <select class="select2 select2-multiple w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" id="employee_id" name="employee_id[]">
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{in_array($emp->id, $event->attendees->pluck('id')->toArray())  ? 'selected' : ''}}>{{ ucwords($emp->name) }}
                        </option>
                    @endforeach
                        </select>
            </div>
        </div>
    </div>
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-12">
        <div class="md:col-span-4">
            <div class="space-y-2">
                <div class="mb-2">
                    <label class="control-label block text-sm font-medium text-gray-700">@lang('modules.zoommeeting.hostVideoStatus')</label>
                </div>
                <div class="inline-flex items-center gap-2">
                    <input type="radio" name="host_video" id="edit-host_video1" value="1" {{ $event->host_video ? "checked" : "" }}>
                    <label for="edit-host_video1" class="text-sm text-gray-700"> @lang('app.enable') </label>
                </div>
                <div class="inline-flex items-center gap-2">
                    <input type="radio" name="host_video" id="edit-host_video2" value="0"{{ !$event->host_video ? "checked" : "" }}>
                    <label for="edit-host_video2" class="text-sm text-gray-700"> @lang('app.disable') </label>
                </div>
            </div>
        </div>
        <div class="md:col-span-4">
            <div class="space-y-2">
                <div class="mb-2">
                    <label class="control-label block text-sm font-medium text-gray-700">@lang('modules.zoommeeting.participantVideoStatus')</label>
                </div>
                <div class="inline-flex items-center gap-2">
                    <input type="radio" name="participant_video" id="edit-participant_video1" value="1" {{ $event->participant_video ? "checked" : "" }}>
                    <label for="edit-participant_video1" class="text-sm text-gray-700"> @lang('app.enable') </label>
                </div>
                <div class="inline-flex items-center gap-2">
                    <input type="radio" name="participant_video" id="edit-participant_video2" value="0" {{ !$event->participant_video ? "checked" : "" }}>
                    <label for="edit-participant_video2" class="text-sm text-gray-700"> @lang('app.disable') </label>
                </div>
            </div>
        </div>
        <div class="md:col-span-4">
            <div class="space-y-1">
                  <label class="control-label block text-sm font-medium text-gray-700">@lang('modules.zoommeeting.meetingHost')</label>
                  <select class="select2 w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" id="created_by" name="created_by">
                      @foreach($employees as $emp)
                          <option @if($emp->id == $event->created_by)
                              selected
                              @endif value="{{ $emp->id }}">{{ ucwords($emp->name) }}</option>
                      @endforeach
                  </select>
            </div>
        </div>
    </div>  

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-12">
        <div class="md:col-span-4">
            <div class="form-group-inline">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="send_reminder" value="0">
                    <input type="checkbox" value="1" name="send_reminder" {{ ($event->send_reminder == '1')? "checked" : "" }} id ="edit-send_reminder" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    @lang('modules.zoommeeting.reminder')
                </label>
                
               
            </div>
        </div>
        
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-12" id="edit-reminder-fields" style="display: none;">
        <div class="md:col-span-3">
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">@lang('modules.zoommeeting.remindBefore')</label>
                <input type="number" min="1" value="{{$event->remind_time}}" name="remind_time" id="remind_time" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>
        <div class="md:col-span-3">
            <div class="repeat_type_dropdown space-y-1">
                <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                
                <select name="remind_type" id="remind_type" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="day" {{$event->remind_type == "day" ? 'selected' : ''}}>@lang('modules.zoommeeting.day')</option>
                    <option value="hour" {{$event->remind_type == "hour" ? 'selected' : ''}}>@lang('modules.zoommeeting.hour')</option>
                    <option value="minute" {{$event->remind_type == "minute" ? 'selected' : ''}}>@lang('modules.zoommeeting.minute')</option>
                </select>
            </div>
        </div>
    </div>
    
    </form>
</div>
<div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4">
    <button type="button" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50" data-dismiss="modal">@lang('app.close')</button>
    <button type="button" id="sub" class="edit-meeting inline-flex items-center rounded-md border border-emerald-600 bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">@lang('app.submit')</button>
</div>

<script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/multiselect/js/jquery.multi-select.js') }}"></script>
<script src="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}" type="text/javascript"></script>
<script>
      $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        },
        width: '100%'
    });
  // Datepicker set
     $('#start_date, #end_date').bootstrapMaterialDatePicker
    ({
        time: false,
        clearButton: true,
        minDate : new Date()
    });
    $('#start_time, #end_time').bootstrapMaterialDatePicker
    ({
        date: false,
        shortTime: true,   // look it
        format: 'HH:mm',
        switchOnClick: true
    });
 $(function() {
        
        
        $('.edit-meeting').click(function () {
            var id = $("#id_field").val();
            var url = "{{ route('admin.zoom-meeting.update', ':id') }}";
                url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                container: '#editMeeting',
                type: "POST",
                data: $('#editMeeting').serialize(),
                success: function (response) {
                    if(response.status == 'success'){
                        window.location.reload();
                    }
                }
            })
        });
        $('#edit-repeat-meeting').is(':checked') ? $('#repeat-fields').show() : $('#repeat-fields').hide();
        $('#edit-send_reminder').is(':checked') ? $('#edit-reminder-fields').show() : $('#edit-reminder-fields').hide();

        $('#edit-repeat-meeting').change(function () {
            if($(this).is(':checked')){
                $('#edit-repeat-fields').show();
            }
            else{
                $('#edit-repeat-fields').hide();
            }
        })
        @if($event->send_reminder)
            $('#edit-reminder-fields').show(); 
        @endif
        $('#edit-send_reminder').on('change', function(){
            $('#edit-reminder-fields').toggle(this.checked);
        });

        $('#add_category').click(function () {
            var url = '{{ route('admin.category.create')}}';
        $('#modelHeading').html('...');
        $.ajaxModal('#categoryModal', url);
        
    });
     })
   
</script>
