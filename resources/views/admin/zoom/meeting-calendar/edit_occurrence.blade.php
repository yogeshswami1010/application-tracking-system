<div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-xl leading-none text-gray-500 transition hover:bg-gray-100 hover:text-gray-700" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="text-lg font-semibold text-gray-800"><i class="icon-pencil mr-2"></i> @lang('zoom::modules.zoommeeting.editMeeting')</h4>
</div>
<div class="px-6 py-5">
    <form id="editMeeting" class="ajax-form" method="POST">
    @csrf
        
    <input type="hidden" name ="id_field" id ="id_field"  value="{{$event->id}}" >
    <div class="grid grid-cols-1 gap-4">
        <div class="col-span-1">
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">@lang('zoom::modules.zoommeeting.meetingName')</label>
                <p class="text-sm text-gray-700">{{$event->meeting_name}}</p>
            </div>
        </div>       

    </div>
    
    <div class="mt-4 grid grid-cols-1 gap-4">
        <div class="col-span-1">
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">@lang('app.description')</label>
                <p class="text-sm text-gray-700">{{ $event->description ?? "--" }}</p>
            </div>
        </div>
    </div>
    
    <div class="mt-4 grid grid-cols-1 gap-4">
        <div class="col-span-1">
            <div class="space-y-1">
                  <label class="control-label block text-sm font-medium text-gray-700">@lang('zoom::modules.zoommeeting.meetingHost')</label>
                  <select class="select2 w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" id="created_by_2" name="created_by">
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
        <div class="md:col-span-3">
            <div class="space-y-1">
                <label class="required block text-sm font-medium text-gray-700">@lang('zoom::modules.zoommeeting.startOn')</label>
                <input type="text" name="start_date" id="start_date" value="{{ $event->start_date_time->format($global->date_format) }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" autocomplete="none">
            </div>
        </div>
        <div class="md:col-span-3">
            <div class="input-group bootstrap-timepicker timepicker space-y-1">
                <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                <input type="text" name="start_time"  value="{{ $event->start_date_time->format($global->time_format) }}"  id="start_time" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>

        <div class="md:col-span-3">
            <div class="space-y-1">
                <label class="required block text-sm font-medium text-gray-700">@lang('zoom::modules.zoommeeting.endOn')</label>
                <input type="text" name="end_date" id="end_date" value="{{ $event->end_date_time->format($global->date_format) }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" autocomplete="none">
            </div>
        </div>
        <div class="md:col-span-3">
            <div class="input-group bootstrap-timepicker timepicker space-y-1">
                <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                <input type="text" name="end_time" id="end_time" value="{{ $event->end_date_time->format($global->time_format) }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    </form>
</div>
<div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4">
    <button type="button" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50" data-dismiss="modal">@lang('app.close')</button>
    <button type="button" id="sub" class="edit-meeting inline-flex items-center rounded-md border border-emerald-600 bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">@lang('app.submit')</button>
</div>

<script src="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/cbpFWTabs.js') }}"></script>
<link rel="stylesheet" href="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.css') }}">
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/multiselect/js/jquery.multi-select.js') }}"></script>
<script src="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.js') }}"></script>
<script>
    $(function() {
        jQuery('#start_date, #end_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: '{{ $global->date_picker_format }}',
            
        })
        $('#start_time, #end_time').timepicker({
            @if($global->time_format == 'H:i')
            showMeridian: false,
            @endif
            
        });
        $("#created_by_2").select2({
            formatNoMatches: function () {
                return "{{ __('messages.noRecordFound') }}";
            }
        });
        
        $('.edit-meeting').click(function () {
            var id = $("#id_field").val();
            var url = "{{ route('admin.zoom-meeting.updateOccurrence', ':id') }}";
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
       
    })
</script>
