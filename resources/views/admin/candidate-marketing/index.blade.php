@extends('layouts.app')

@push('head-script')
<style>
    .cm-wrap { padding: 0; }
    .cm-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:18px; }
    .cm-header h1 { font-size:20px;font-weight:700;color:#1A1E2E;margin:0;display:flex;align-items:center;gap:9px; }
    .cm-header p { font-size:12.5px;color:#8A94A6;margin:3px 0 0; }
    .cm-filters { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;background:#fff;border:1px solid #E8E6E1;border-radius:12px;padding:12px 14px; }
    .cm-filters select, .cm-filters input { padding:7px 11px;border-radius:9px;border:1px solid #E2DED8;font-size:12.5px;font-family:'Plus Jakarta Sans',sans-serif;color:#374151;min-width:160px; }
    .cm-table-wrap { background:#fff;border:1px solid #E8E6E1;border-radius:14px;overflow:hidden; }
    .ja-row-actions { display:flex;gap:5px; }
    .ja-act-btn { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;border:1px solid #E2DED8;background:#fff;color:#5A6478;cursor:pointer;text-decoration:none;font-size:12px;transition:background .15s; }
    .ja-act-btn:hover { background:#F8F7F4; }
    .ja-act-btn.reject:hover { background:#FEF2F2;color:#EF4444;border-color:#FECACA; }
    #candidate-marketing-table th, #candidate-marketing-table td { padding:10px 14px;font-size:13px; }
</style>
@endpush

@section('content')
<div class="ja-board-scope -mx-4 -mt-2 flex min-h-[calc(100dvh-9.5rem)] flex-col bg-[#EEF0F5] sm:-mx-6">
    <div class="flex min-h-0 flex-1 flex-col overflow-hidden px-5 pb-5 pt-5 sm:px-6">

        <div class="cm-wrap">
            <div class="cm-header">
                <div>
                    <h1><i class="fa fa-bullhorn" style="color:#C2410C;font-size:18px"></i> Candidate Marketing</h1>
                    <p>Applicants flagged for marketing — still visible on the regular Job Applications board too.</p>
                </div>
            </div>

            <div class="cm-filters">
                <select id="cm-filter-jobs">
                    <option value="all">All Jobs</option>
                    @foreach($jobs as $job)
                        <option value="{{ $job->id }}">{{ ucfirst($job->title) }}</option>
                    @endforeach
                </select>

                <select id="cm-filter-company">
                    <option value="all">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ ucwords($company->company_name) }}</option>
                    @endforeach
                </select>

                <input type="text" id="cm-filter-label" placeholder="Search by marketing label…">
            </div>

            <div class="cm-table-wrap">
                <table id="candidate-marketing-table" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Applied For</th>
                            <th>Marketing Label</th>
                            <th>Status</th>
                            <th>Added On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@push('footer-script')
<script>
$(function () {
    var cmTable = $('#candidate-marketing-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.candidate-marketing.data') }}",
            data: function (d) {
                d.jobs = $('#cm-filter-jobs').val();
                d.company = $('#cm-filter-company').val();
                d.search_label = $('#cm-filter-label').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'full_name', name: 'full_name' },
            { data: 'title', name: 'title' },
            { data: 'marketing_label', name: 'marketing_label' },
            { data: 'status', name: 'status' },
            { data: 'marketing_added_at', name: 'marketing_added_at' },
            { data: 'action', orderable: false, searchable: false },
        ]
    });

    $('#cm-filter-jobs, #cm-filter-company').on('change', function () { cmTable.draw(); });
    var labelTimer;
    $('#cm-filter-label').on('keyup', function () {
        clearTimeout(labelTimer);
        labelTimer = setTimeout(function () { cmTable.draw(); }, 350);
    });

    // Reuse the same right-sidebar applicant profile panel that
    // job-applications/index.blade.php uses (#right-sidebar / .show-detail).
    $('#candidate-marketing-table').on('click', '.show-detail', function () {
        var $sidebar  = $('#right-sidebar');
        var $backdrop = $('#right-sidebar-backdrop');
        $sidebar.removeClass('translate-x-full').addClass('translate-x-0');
        $backdrop.removeClass('hidden').css({ display: 'block', visibility: 'visible' });

        var id  = $(this).data('row-id');
        var url = "{{ route('admin.job-applications.show', ':id') }}".replace(':id', id);
        $.easyAjax({
            type: 'GET', url: url,
            success: function (response) {
                if (response.status === 'success') {
                    $('#right-sidebar-content').html(response.view);
                }
            }
        });
    });

    window.cmRemoveFromMarketing = function (id) {
        swal({
            title: "@lang('errors.areYouSure')",
            text: "Remove this applicant from Candidate Marketing?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Remove",
            cancelButtonText: "@lang('app.cancel')",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                $.easyAjax({
                    type: 'POST',
                    url: "{{ route('admin.candidate-marketing.remove', ':id') }}".replace(':id', id),
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        if (response.status === 'success') cmTable.draw(false);
                    }
                });
            }
        });
    };
});
</script>
@endpush