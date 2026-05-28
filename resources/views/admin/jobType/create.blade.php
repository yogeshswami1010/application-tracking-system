<div class="job-aux-manage space-y-5">
    <div class="overflow-hidden rounded-xl border border-gray-200">
        <div class="max-h-[min(40vh,280px)] overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-100 text-left text-[13px]">
                <thead class="sticky top-0 z-[1] bg-[#F8F7F4]">
                    <tr>
                        <th class="px-3 py-2.5 font-bold uppercase tracking-wide text-[10.5px] text-[#8892A0]">#</th>
                        <th class="px-3 py-2.5 font-bold uppercase tracking-wide text-[10.5px] text-[#8892A0]">@lang('modules.jobs.jobType')</th>
                        <th class="w-[1%] whitespace-nowrap px-3 py-2.5 text-right font-bold uppercase tracking-wide text-[10.5px] text-[#8892A0]">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($jobTypes as $key => $jobType)
                        <tr id="dept-{{ $jobType->id }}" class="transition hover:bg-[#FAFAF8]/80">
                            <td class="px-3 py-2.5 text-[#8892A0]">{{ $key + 1 }}</td>
                            <td class="px-3 py-2.5 font-medium text-[#0F1F3D]" id="deptName{{ $jobType->id }}">{{ ucwords($jobType->job_type) }}</td>
                            <td class="px-3 py-2.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" data-dept-id="{{ $jobType->id }}" class="job-type-edit-row inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#2563EB] text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30" title="@lang('app.edit')" aria-label="@lang('app.edit')">
                                        <i class="fa fa-pencil text-xs" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" data-dept-id="{{ $jobType->id }}" class="job-type-delete-row inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-500 text-white shadow-sm transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500/30" title="@lang('app.delete')" aria-label="@lang('app.delete')">
                                        <i class="fa fa-times text-xs" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-8 text-center text-[13px] text-[#8892A0]">@lang('messages.notFound')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-dashed border-gray-200 bg-[#FAFAF8]/50 p-4">
        <form id="createdepartment" class="ajax-form space-y-4" method="post">
            @csrf
            <div>
                <label for="job_type_name" class="mb-1.5 block text-[11.5px] font-bold uppercase tracking-wide text-slate-500">@lang('modules.jobs.jobType')</label>
                <input type="text" name="job_type" id="job_type_name" autocomplete="off" class="form-control w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-[13px] text-[#0F1F3D] placeholder:text-gray-300 outline-none transition-all focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/10" placeholder="@lang('modules.jobs.jobType')">
                <input type="hidden" name="edit_id" id="edit_id">
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="save-department" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-4 py-2.5 text-[13px] font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <i class="fa fa-check text-xs" aria-hidden="true"></i>
                    @lang('app.save')
                </button>
                <button type="button" id="update-department" hidden class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-[13px] font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <i class="fa fa-check text-xs" aria-hidden="true"></i>
                    @lang('app.update')
                </button>
                <button type="button" id="cancel-edit-department" hidden class="text-[13px] font-semibold text-[#8892A0] transition hover:text-[#0F1F3D]">@lang('app.cancel')</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    function refreshJobTypeSelect(rData) {
        var opts = ['<option value="">—</option>'];
        $.each(rData, function (i, value) {
            opts.push('<option value="' + value.id + '">' + $('<div/>').text(value.job_type).html() + '</option>');
        });
        var $jt = $('#job_type');
        if ($jt.data('select2')) {
            $jt.select2('destroy');
        }
        $jt.html(opts.join(''));
    }

    function closeModal() {
        if (typeof window.closeJobAuxModal === 'function') {
            window.closeJobAuxModal();
        }
    }

    $('#save-department').on('click', function () {
        $.easyAjax({
            url: '{{ route('admin.job-type.store') }}',
            container: '#createdepartment',
            type: 'POST',
            data: $('#createdepartment').serialize(),
            success: function (response) {
                refreshJobTypeSelect(response.data);
                closeModal();
            }
        });
    });

    $('#update-department').on('click', function () {
        var id = $('#edit_id').val();
        var url = "{{ route('admin.job-type.update', ':id') }}".replace(':id', id);
        var name = $('#job_type_name').val();

        $.easyAjax({
            url: url,
            container: '#createdepartment',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'PUT', job_type: name },
            success: function (response) {
                if (response.status === 'success') {
                    refreshJobTypeSelect(response.data);
                    $('#deptName' + id).text(name);
                    $('#edit_id').val('');
                    $('#job_type_name').val('');
                    $('#update-department').prop('hidden', true);
                    $('#cancel-edit-department').prop('hidden', true);
                    $('#save-department').prop('hidden', false);
                    closeModal();
                }
            }
        });
    });

    $('#cancel-edit-department').on('click', function () {
        $('#edit_id').val('');
        $('#job_type_name').val('');
        $('#update-department').prop('hidden', true);
        $('#cancel-edit-department').prop('hidden', true);
        $('#save-department').prop('hidden', false);
    });

    $(document).off('click.jobTypeAux', '.job-type-edit-row').on('click.jobTypeAux', '.job-type-edit-row', function () {
        var id = $(this).data('dept-id');
        $('#edit_id').val(id);
        $('#job_type_name').val($.trim($('#deptName' + id).text()));
        $('#update-department').prop('hidden', false);
        $('#cancel-edit-department').prop('hidden', false);
        $('#save-department').prop('hidden', true);
    });

    $(document).off('click.jobTypeAux', '.job-type-delete-row').on('click.jobTypeAux', '.job-type-delete-row', function () {
        var id = $(this).data('dept-id');
        swal({
            title: "@lang('errors.areYouSure')",
            text: "@lang('errors.deleteWarning')",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: "@lang('app.delete')",
            cancelButtonText: "@lang('app.cancel')",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (!isConfirm) {
                return;
            }
            var url = "{{ route('admin.job-type.destroy', ':id') }}".replace(':id', id);
            $.easyAjax({
                type: 'POST',
                url: url,
                data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                success: function (response) {
                    if (response.status === 'success') {
                        $('#dept-' + id).fadeOut(200, function () {
                            $(this).remove();
                        });
                        refreshJobTypeSelect(response.data);
                    }
                }
            });
        });
    });
})();
</script>
