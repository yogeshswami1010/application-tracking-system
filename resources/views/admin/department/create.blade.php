<div class="flex items-center justify-between p-4 border-b border-gray-200">
    <h4 class="text-lg font-semibold text-gray-900"><i class="icon-plus"></i> @lang('app.department')</h4>
    <button type="button" class="text-gray-400 hover:text-gray-600" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
</div>
<div class="p-4">
    <div class="overflow-x-auto mb-4">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@lang('app.department')</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@lang('app.action')</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($departments as $key=>$department)
                <tr id="dept-{{ $department->id }}">
                    <td class="px-6 py-4 whitespace-nowrap">{{ $key+1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap" id="deptName{{ $department->id }}">{{ ucwords($department->name) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="javascript:;" data-dept-id="{{ $department->id }}" class="btn btn-sm btn-danger delete-department"><i class="fa fa-times"></i> </a>
                        <a href="javascript:;" data-dept-id="{{ $department->id }}" class="btn btn-sm btn-info edit-department"><i class="fa fa-pencil"></i> </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center">@lang('messages.notFound')</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <hr class="my-4">
    <form id="createdepartment" class="ajax-form space-y-4" method="post">
        {{ csrf_field() }}
        <div class="form-body">
            <div class="form-group">
                <label class="form-label">@lang('app.department')</label>
                <input type="text" name="name" id="name" class="form-control">
                <input type="hidden" name="edit_id" id="edit_id">
            </div>
        </div>
        <div class="form-actions flex items-center gap-2">
            <button type="button" id="save-department" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.save')</button>
            <button type="button" id="update-department" class="btn btn-success hidden"> <i class="fa fa-check"></i> @lang('app.update')</button>
        </div>
    </form>

</div>
<div class="flex items-center justify-end gap-2 p-4 border-t border-gray-200">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('app.close')</button>
</div>


<script>
    // Select 2 init.
    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    // Save department
    $('#save-department').click(function () {
        $.easyAjax({
            url: '{{route('admin.departments.store')}}',
            container: '#createdepartment',
            type: "POST",
            data: $('#createdepartment').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    var options = [];
                    var rData = [];
                    rData = response.data;
                    $.each(rData, function( index, value ) {
                        var selectData = '';
                        selectData = '<option value="'+value.id+'">'+value.name+'</option>';
                        options.push(selectData);
                    });

                    $('#department').html(options);
                    if ($('#department').hasClass('select2')) {
                        $('#department').select2();
                    }
                    $('#addDepartmentModal').modal('hide');
                }
            }
        })
    })

    // Update department
    $('#update-department').click(function () {
        var id = $('#edit_id').val();
        var url = "{{ route('admin.departments.update',':id') }}";
        url = url.replace(':id', id);
        var token = '{{ csrf_token() }}';
        var name = $('#name').val();;

        $.easyAjax({
            url: url,
            container: '#createdepartment',
            type: "POST",
            data: {'_token':token, '_method':'PUT','name':name  },
            success: function (response) {
                if(response.status == 'success'){
                    var options = [];
                    var rData = [];
                    rData = response.data;
                    $.each(rData, function( index, value ) {
                        var selectData = '';
                        selectData = '<option value="'+value.id+'">'+value.name+'</option>';
                        options.push(selectData);
                    });

                    $('#department').html(options);
                    if ($('#department').hasClass('select2')) {
                        $('#department').select2();
                    }

                    $('#deptName'+id).html(name);// Set name in table row
                    $('#edit_id').val('');// Set edit id field blank
                    $('#name').val('');// Set name field blank
                    $('#update-department').addClass('hidden');
                    $('#save-department').removeClass('hidden');
                }
            }
        })
    })

    // Edit department
    $('body').on('click', '.edit-department', function(){
        var id = $(this).data('dept-id');
        $('#edit_id').val(id);// Set id in edit id field
        $('#name').val($('#deptName'+id).html());// Set name field by edit name
        $('#update-department').removeClass('hidden');
        $('#save-department').addClass('hidden');
    });

    // Delete Department
    $('body').on('click', '.delete-department', function(){
        var id = $(this).data('dept-id');
        swal({
            title: "@lang('errors.areYouSure')",
            text: "@lang('errors.deleteWarning')",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "@lang('app.delete')",
            cancelButtonText: "@lang('app.cancel')",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm){
            if (isConfirm) {

                var url = "{{ route('admin.departments.destroy',':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token': token, '_method': 'DELETE'},
                    success: function (response) {
                        if (response.status == "success") {
                            $.unblockUI();
                            $('#dept-'+id).fadeOut();
                            var options = [];
                            var rData = [];
                            rData = response.data;
                            $.each(rData, function( index, value ) {
                                var selectData = '';
                                selectData = '<option value="'+value.id+'">'+value.name+'</option>';
                                options.push(selectData);
                            });

                            $('#department').html(options);
                            if ($('#department').hasClass('select2')) {
                                $('#department').select2();
                            }
                        }
                    }
                });
            }
        });
    });
</script>
