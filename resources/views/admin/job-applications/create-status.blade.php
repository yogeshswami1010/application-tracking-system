<div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
    <h4 class="text-lg font-semibold text-gray-900">
        <i class="icon-plus"></i> @lang('modules.jobApplication.createStatus')
    </h4>
    <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors" onclick="$(this).closest('.modal').addClass('hidden')">
        <i class="fa fa-times"></i>
    </button>
</div>
<div class="p-6">
    <form id="createStatus" class="ajax-form" method="post">
        @csrf
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">@lang('modules.jobApplication.statusName')</label>
                    <input type="text" id="status_name" name="status_name" class="form-control">
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.jobApplication.statusColor')</label>
                    <div id="cp2" class="flex items-center">
                        <input type="text" class="form-control flex-1" name="status_color" value="#DD0F20"/>
                        <span class="ml-2 p-2 border border-gray-300 rounded colorpicker-input-addon cursor-pointer"><i></i></span>
                    </div>
                </div>
                <div class="form-group md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('modules.jobApplication.statusPosition')</label>
                    <select name="status_position" id="status_position" class="select2 form-control">
                        <option value="0">{{'Before '.ucwords($firstStatus->status)}}</option>
                        @foreach ($statuses as $status)
                            <option value="{{$status->position}}">{{'After '.ucwords($status->status)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </form>

</div>
<div class="flex items-center justify-end space-x-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
    <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" onclick="$(this).closest('.modal').addClass('hidden')">@lang('app.close')</button>
    <button type="button" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" onclick="javascript:saveStatus();">@lang('app.submit')</button>
</div>

<script>
    $(function() {
        $('#cp2').colorpicker();
    });
</script>
