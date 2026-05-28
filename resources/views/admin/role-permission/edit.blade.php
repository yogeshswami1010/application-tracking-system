<form id="createProjectCategory" class="ajax-form space-y-4" method="POST" onsubmit="return false;">
    @csrf
    @method('PUT')
    <div>
        <label class="mb-1.5 block text-[11.5px] font-bold uppercase tracking-wide text-[#8892A0]" for="name">@lang('modules.permission.roleName')</label>
        <input type="text" name="name" id="name" required value="{{ $role->name }}"
               class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-[13px] text-[#0F1F3D] outline-none transition-all focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10">
    </div>
    <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4">
        <button type="button" data-dismiss="modal" class="cursor-pointer rounded-xl border-0 bg-gray-100 px-4 py-2.5 text-[13px] font-semibold text-slate-600 transition-colors hover:bg-gray-200">
            @lang('app.cancel')
        </button>
        <button type="button" id="save-category" class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-0 bg-[#2563EB] px-4 py-2.5 text-[13px] font-semibold text-white transition-colors hover:bg-blue-700">
            @lang('app.save')
        </button>
    </div>
</form>

<script>
    $('#save-category').off('click').on('click', function () {
        $.easyAjax({
            url: '{{ route("admin.role-permission.update", $role->id) }}',
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
</script>
