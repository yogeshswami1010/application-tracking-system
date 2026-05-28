<div id="ModalLoginForm" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="$('#ModalLoginForm').addClass('hidden')"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-blue-600">@lang('modules.applicationSetting.formSettings')</h4>
                <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors" onclick="$('#ModalLoginForm').addClass('hidden')">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="p-6">
            <form id="editSettings" class="ajax-form">
            @csrf
                        @method('PUT')
                    
                    <div class="form-group">
                        <label for="address">@lang('modules.applicationSetting.legalTermText')</label>
                        <div>
                            <textarea class="form-control" id="legal_term" name="legal_term" rows="15" placeholder="Enter text ..."></textarea>

                        </div>
                    </div>
                    <div class="form-group">
                        <h4 class="card-title mb-4 text-primary">@lang('modules.applicationSetting.mailSettings')</h4>
                        <div>
                            <label style="margin-left: 0px">Send mail if candidate move to </label>
                            <div>
                                <ul id="assetNameMenu">
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div>
                            <button type="button" id="save-form" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                @lang('app.save')
                            </button>
                            <button type="button" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 ml-2">@lang('app.reset')</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="flex items-center justify-end space-x-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" onclick="$('#ModalLoginForm').addClass('hidden')">Close</button>
            </div>
        </div>
    </div>
</div>

@push('footer-script')
<script src="{{ asset('assets/node_modules_files/bootstrap-select/bootstrap-select.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/html5-editor/wysihtml5-0.3.0.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/html5-editor/bootstrap-wysihtml5.js') }}" type="text/javascript"></script>

<script>
    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route('admin.application-setting.update', $global->id)}}',
            container: '#editSettings',
            type: "POST",
            redirect: true,
            data: $('#editSettings').serialize(),
            file: false
        })
        // $('#ModalLoginForm').modal('toggle'); 
    return false;
    });
</script>

  
@endpush