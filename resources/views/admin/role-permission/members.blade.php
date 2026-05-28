
<div class="flex items-center justify-between p-4 border-b border-gray-200">
    <h4 class="text-lg font-semibold text-gray-900">@lang('modules.permission.addRoleMember')</h4>
    <button type="button" class="text-gray-400 hover:text-gray-600" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="p-4">
    <div class="overflow-x-auto mb-4">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@lang('app.name')</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@lang('app.action')</th>
                </tr>
                </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($role->roleuser as $key=>$ruser)
                    <tr id="ruser-{{ $ruser->user->id }}">
                    <td class="px-6 py-4 whitespace-nowrap">{{ $key+1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ ucwords($ruser->user->name) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ ucwords($role->name) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap"><a href="javascript:;" data-ruser-id="{{ $ruser->user->id }}" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 text-sm delete-category">@lang("app.remove")</a></td>
                    </tr>
                @empty
                    <tr>
                    <td colspan="4" class="px-6 py-4 text-center">@lang('messages.noRoleMemberFound')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    <hr class="my-4">
    <form action="" id="createProjectCategory" class="ajax-form space-y-4" method="POST">
            @csrf
            <input type="hidden" name="role_id" value="{{ $role->id }}">

        <div class="space-y-4">
            <div class="mb-4">
                <h5 class="text-sm font-medium text-gray-700 mb-2">@lang('modules.permission.addMembers')</h5>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary select2 select2-multiple" style="width: 100% !important; " multiple="multiple"
                            data-placeholder="Choose Members" name="user_id[]">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ ucwords($emp->name). ' ['.$emp->email.']' }} @if($emp->id == $user->id)
                                    (YOU) @endif</option>
                        @endforeach
                    </select>
                </div>
            </div>
        <div class="flex items-center justify-end gap-2">
            <button type="button" id="save-category" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"> <i class="fa fa-check"></i> @lang('app.save')</button>
            </div>
        </form>
</div>

<script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/node_modules_files/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('assets/node_modules_files/multiselect/js/jquery.multi-select.js') }}"></script>

<script>
    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    $('.delete-category').click(function () {
        var userId = $(this).data('ruser-id');
        var roleId = '{{ $role->id }}';
        var url = "{{ route('admin.role-permission.detachRole') }}";

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            type: 'POST',
            url: url,
            data: {'_token': token, 'userId': userId, 'roleId': roleId},
            success: function (response) {
                if (response.status == "success") {
                    $.unblockUI();
//                                    swal("Deleted!", response.message, "success");
                    window.location.reload();
                }
            }
        });
    });

    $('#save-category').click(function () {
        $.easyAjax({
            url: '{{route('admin.role-permission.assignRole')}}',
            container: '#createProjectCategory',
            type: "POST",
            data: $('#createProjectCategory').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    });
</script>