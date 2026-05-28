<link rel="stylesheet" href="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}">
<link rel="stylesheet" href="{{ asset('assets/node_modules_files/html5-editor/bootstrap-wysihtml5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/node_modules_files/multiselect/css/multi-select.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/iCheck/all.css') }}">
<div id="myModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="$('#myModal').addClass('hidden')"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-blue-600">@lang('app.refreshjob')</h4>
                <button type="button" class="text-gray-400 hover:text-gray-600" data-dismiss="modal">&times;</button>
            </div>
            <div class="p-6">
                <form id="editDate" class="ajax-form space-y-4" method="post">
                @csrf
                <input type ="hidden" id = "hidden_id" name="id">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="form-group">
                                    <label>@lang('app.startDate')</label>
                                    <input type="text" class="form-control" id="date-start" value="" name="start_date">
                                </div>
                            </div>

                            <div>
                                <div class="form-group">
                                    <label class="form-label">@lang('app.endDate')</label>
                                    <input type="text" class="form-control" id="date-end" name="end_date">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="flex items-center justify-end space-x-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                <button type="button" id="save-form" class="btn btn-success">
                    @lang('app.save')
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@push('footer-script')
<script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}" type="text/javascript"></script>


<script>
    // Datepicker set
    $('#date-end').bootstrapMaterialDatePicker
    ({
        time: false,
        clearButton: true,
        minDate : new Date()
    });
    $('#date-start').bootstrapMaterialDatePicker({
        time: false,
        clearButton: true,
        minDate : new Date()
    }).on('change', function(e, date) {
        $('#date-end').bootstrapMaterialDatePicker('setMinDate', date);
        $('#date-end').val(moment(date).add(1, 'month').format('YYYY-MM-DD'));
    });

    // Timepicker Set
    $('#scheduleTime').bootstrapMaterialDatePicker({
        date: false,
        shortTime: true, // look it
        format: 'HH:mm',
        switchOnClick: true
    });
    //onclick to save form
    $('#save-form').click(function() {
        var id = $('#hidden_id').val();
        var url = "{{ route('admin.jobs.refreshDate',':id') }}";
        url = url.replace(':id', id);
        var token = "{{ csrf_token() }}";
       
        $.easyAjax({
            url: url,
            container: '#editDate',
            type: "POST",
            redirect: true,
            data: $('#editDate').serialize(),
            success: function (response) {
                console.log(response);
                    if (response.status == 'success') {
                        $('#myModal').modal('hide');
                        window.location.reload();
                       
                    }
                }
        });
        
    });
</script>


@endpush