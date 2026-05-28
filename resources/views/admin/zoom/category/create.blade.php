<style>
    .modal {
    overflow-x: hidden;
    overflow-y: auto;
}
</style>

@if(request('inModal') != '1')
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <h4 class="text-lg font-semibold text-gray-900"><i class="icon-plus"></i> @lang('app.category')</h4>
        <button type="button" class="text-gray-400 hover:text-gray-600" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
    </div>
@endif
<div class="p-4">
    <div class="overflow-x-auto mb-4">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@lang('app.category')</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@lang('app.action')</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($categories as $key=>$category)
                <tr id="cat-{{ $category->id }}">
                    <td class="px-6 py-4 whitespace-nowrap">{{ $key+1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ ucwords($category->category_name) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap"><a href="javascript:;" data-cat-id="{{ $category->id }}" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 text-sm delete-category">@lang("app.remove")</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center">@lang('modules.message.noCategoryAdded')</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <form id="createClientCategory" class="ajax-form space-y-4" method="POST">
        @csrf
        <div class="space-y-4">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1 required">@lang('app.add') @lang('app.category')</label>
                <input type="text" name="category_name" id="category_name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
            </div>
        </div>
        <div class="flex items-center justify-end gap-2">
            <button type="button" id="save-category" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"> <i class="fa fa-check"></i> @lang('app.save')</button>
        </div>
    </form>
</div>

<script>
    function hideCategoryModal() {
        // Bootstrap modal (if plugin exists)
        if (typeof $.fn.modal === 'function') {
            $('#categoryModal').modal('hide');
        }

        // Custom overlay fallback (works on our meeting create/edit pages)
        $('#categoryModal').addClass('hidden').hide().attr('aria-hidden', 'true');

        if ($('#categoryModalBody').length) {
            $('#categoryModalBody').html('Loading...');
        }
    }


    $('.delete-category').click(function () {
        var id = $(this).data('cat-id');
        var url = "{{ route('admin.category.destroy',':id') }}";
        url = url.replace(':id', id);

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            type: 'POST',
            url: url,
            data: {'_token': token, '_method': 'DELETE'},
            success: function (response) {
                if (response.status == "success") {
                    $.unblockUI();
                    $('#cat-'+id).fadeOut();
                    var options = [];
                    var rData = [];
                    rData = response.data;
                   
                    $.each(rData, function( index, value ) {
                        var selectData = '';
                        selectData = '<option value="'+value.id+'">'+value.category_name+'</option>';
                        
                        options.push(selectData);
                    });
                    $('#category_id_edit').html(options);
                    $('#category_id').html(options);
                    hideCategoryModal();
                }
            }
        });
    });

    $('#save-category').click(function () {
        $.easyAjax({
            url: '{{route('admin.category.store')}}',
            container: '#createClientCategory',
            type: "POST",
            data: $('#createClientCategory').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                        var options = [];
                        var rData = [];
                        rData = response.data;

                        var newId = (rData && rData.length) ? rData[rData.length - 1].id : null;
                        $.each(rData, function( index, value ) {
                            var selectData = '';
                            selectData = '<option value="'+value.id+'">'+value.category_name+'</option>';
                            options.push(selectData);
                        });
                        $('#category_id_edit').html(options);
                        $('#category_id').html(options);

                        if (newId) {
                            $('#category_id_edit').val(newId).trigger('change');
                            $('#category_id').val(newId).trigger('change');
                        }

                        // Hide modal (works for both bootstrap and custom modals)
                        hideCategoryModal();
                }
            }
        })
    });
</script>