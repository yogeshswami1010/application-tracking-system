<p class="text-[11.5px] text-[#8892A0]">{{ __('modules.permission.manageRolesHint') }}</p>

<div class="mt-4 flex max-h-60 flex-col gap-2 overflow-y-auto pr-0.5">
    @forelse($roles as $key => $role)
        <div id="role-{{ $role->id }}" class="flex items-center justify-between rounded-xl border px-3 py-2.5 {{ $role->id <= 1 ? 'border-amber-100 bg-amber-50' : 'border-gray-200 bg-[#F8F7F4]' }}">
            <div class="flex min-w-0 items-center gap-2.5">
                @php
                    $rpDot = ['bg-amber-500', 'bg-blue-500', 'bg-emerald-500', 'bg-purple-500', 'bg-rose-500', 'bg-teal-500'][$key % 6];
                @endphp
                <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $rpDot }}"></span>
                <span class="truncate text-[13px] font-semibold {{ $role->id <= 1 ? 'text-amber-800' : 'text-[#0F1F3D]' }}">{{ ucwords($role->name) }}</span>
                @if($role->id <= 1)
                    <span class="shrink-0 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-600">{{ __('modules.permission.protectedBadge') }}</span>
                @endif
            </div>
            @if($role->id > 1)
                <div class="flex shrink-0 items-center gap-1">
                    <button type="button" data-role-id="{{ $role->id }}" class="edit-category rounded-lg border-0 bg-[#EFF6FF] px-2.5 py-1.5 text-[11.5px] font-semibold text-[#2563EB] transition-colors hover:bg-blue-100">
                        @lang('app.edit')
                    </button>
                    <button type="button" data-role-id="{{ $role->id }}" class="delete-category flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg border-0 bg-red-50 transition-colors hover:bg-red-100" title="@lang('app.remove')">
                        <svg class="h-3 w-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            @else
                <svg class="h-4 w-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            @endif
        </div>
    @empty
        <p class="py-6 text-center text-[13px] text-[#8892A0]">@lang('messages.noRoleFound')</p>
    @endforelse
</div>

<div class="mt-4 flex flex-col gap-2 border-t border-gray-100 pt-4">
    <form id="createProjectCategory" class="ajax-form flex gap-2" method="POST" onsubmit="return false;">
        @csrf
        <input type="text" name="name" id="name" required
               placeholder="{{ __('modules.permission.newRolePlaceholder') }}"
               class="min-w-0 flex-1 rounded-xl border border-gray-200 px-3.5 py-2.5 text-[13px] text-[#0F1F3D] outline-none transition-all placeholder:text-gray-300 focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10">
        <button type="button" id="save-category" class="inline-flex shrink-0 cursor-pointer items-center gap-1.5 rounded-xl border-0 bg-[#2563EB] px-4 py-2.5 text-[13px] font-semibold text-white transition-colors hover:bg-blue-700">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            @lang('app.add')
        </button>
    </form>
</div>

<div class="mt-4 flex items-center justify-end border-t border-gray-100 pt-4">
    <button type="button" data-dismiss="modal" class="cursor-pointer rounded-xl border-0 bg-gray-100 px-5 py-2.5 text-[13px] font-semibold text-slate-600 transition-colors hover:bg-gray-200">
        {{ __('modules.permission.done') }}
    </button>
</div>

<script>
    $('.delete-category').off('click').on('click', function () {
        var roleId = $(this).data('role-id');
        var url = "{{ route('admin.role-permission.deleteRole') }}";
        $.easyAjax({
            type: 'POST',
            url: url,
            data: {_token: "{{ csrf_token() }}", roleId: roleId},
            success: function (response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        });
    });

    $('#save-category').off('click').on('click', function () {
        $.easyAjax({
            url: '{{ route('admin.role-permission.storeRole') }}',
            container: '#createProjectCategory',
            type: "POST",
            data: $('#createProjectCategory').serialize(),
            success: function (response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        });
    });

    $('.edit-category').off('click').on('click', function () {
        var id = $(this).data('role-id');
        var url = '{{ route("admin.role-permission.edit", ":id")}}'.replace(':id', id);
        $('#modelHeading').html('@lang('app.edit')');
        $.ajaxModal('#managePermissionModal', url);
    });
</script>
