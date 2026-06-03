@extends('layouts.app')

@php
    $jaDefaultStart = now()->subDays(30)->format('Y-m-d');
    $jaDefaultEnd   = now()->format('Y-m-d');
@endphp

@push('head-script')
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/multiselect/css/multi-select.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    @include('admin.job-applications.partials.select2-filter-skin')
    <style>
        [type="checkbox"]:not(:checked), [type="checkbox"]:checked { position: absolute; left: auto !important; }
        .datepicker { z-index: 9999 !important; }
        .ja-board-scope { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        .ja-filter-btn-active {
            border-color: #2563eb !important;
            background: #eff6ff !important;
            color: #2563eb !important;
        }
        .ja-filter-active-count {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            background: #2563eb;
            color: #fff;
            display: none;
        }
        .ja-filter-active-count.show { display: inline-flex; }
        .hidden { display: none !important; visibility: hidden !important; }
    </style>
@endpush

@section('content')
<div class="ja-board-scope -mx-4 -mt-2 flex min-h-[calc(100dvh-9.5rem)] flex-col bg-[#EEF0F5] sm:-mx-6">
    <div class="flex min-h-0 flex-1 flex-col overflow-hidden px-5 pb-5 pt-5 sm:px-6">

        {{-- Toolbar --}}
        <div class="mb-[18px] flex flex-shrink-0 flex-wrap items-center gap-2.5">

            <button type="button" id="toggle-filter"
                class="toggle-filter inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB] focus:outline-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                @lang('app.filterResults')
                <span class="ja-filter-active-count" id="ja-table-filter-active-count">0</span>
            </button>

            <button type="button" onclick="exportJobApplication()"
                class="inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB]">
                <i class="fa fa-upload"></i> @lang('menu.export')
            </button>

            <button type="button" class="btn btn-sm btn-danger deleteButton hidden" id="deleteAllSelectedRecords">
                @lang('app.delete')
            </button>
        </div>

        {{-- Filter Bar --}}
        <div id="ja-table-filter-bar" class="border-b border-[#E8E6E1] bg-white">
            <div class="px-5 sm:px-6">
                <div class="flex items-center justify-between gap-2 pb-3 pt-0.5">
                    <h4 class="text-[13px] font-bold text-[#1A1E2E]">@lang('app.filterBy')</h4>
                    <button type="button" class="toggle-filter flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-[#EEF0F5]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="filter-form" class="flex flex-wrap items-end gap-3.5 pb-3">

                    {{-- Skill search --}}
                    <div class="flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('modules.applicationArchive.enterSkill')</label>
                        <input id="skill" class="form-control rounded-[10px] border-[1.5px] border-[#E2DED8] bg-[#F8F7F4] text-[13px]"
                               type="text" name="skill" placeholder="@lang('modules.applicationArchive.enterSkill')">
                    </div>

                    {{-- Company --}}
                    <div class="flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.company')</label>
                        <select class="select2 w-full" name="company" id="company">
                            <option value="all">@lang('modules.jobApplication.allCompany')</option>
                            @forelse($companies as $company)
                                <option value="{{ $company->id }}">{{ ucfirst($company->company_name) }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>

                    {{-- Jobs --}}
                    <div class="flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('menu.jobs')</label>
                        <select class="select2 w-full" name="jobs" id="jobs">
                            <option value="all">@lang('modules.jobApplication.allJobs')</option>
                            @forelse($jobs as $job)
                                <option value="{{ $job->id }}">{{ ucfirst($job->title) }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>

                    {{-- Location --}}
                    <div class="flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('menu.locations')</label>
                        <select class="select2 w-full" name="location" id="location">
                            <option value="all">@lang('modules.jobApplication.allLocation')</option>
                            @forelse($locations as $location)
                                <option value="{{ $location->id }}">{{ ucfirst($location->location) }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>

                    {{-- Question --}}
                    <div class="flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[240px]">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('modules.jobApplication.allQuestion')</label>
                        <select class="select2 w-full" name="question" id="questions">
                            <option value="all">@lang('modules.jobApplication.allQuestion')</option>
                            @forelse($questions as $question)
                                <option value="{{ $question->id }}">{{ ucfirst($question->question) }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>

                    {{-- Question value (shown when a question is selected) --}}
                    <div class="hidden flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]" id="question_value">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.filterBy')</label>
                        <input type="text" class="form-control rounded-[10px] border-[1.5px] border-[#E2DED8] bg-[#F8F7F4] text-[13px]"
                               name="question_value" id="question-value" placeholder="">
                    </div>

                    {{-- Date Range --}}
                    <div class="flex min-w-[140px] flex-1 flex-col gap-1 sm:max-w-[180px]">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.startDate')</label>
                        <input type="text" id="start-date" name="start_date"
                               class="form-control rounded-[10px] border-[1.5px] border-[#E2DED8] bg-[#F8F7F4] text-[13px]"
                               value="{{ $jaDefaultStart }}" autocomplete="off" placeholder="YYYY-MM-DD">
                    </div>

                    <div class="flex min-w-[140px] flex-1 flex-col gap-1 sm:max-w-[180px]">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.endDate')</label>
                        <input type="text" id="end-date" name="end_date"
                               class="form-control rounded-[10px] border-[1.5px] border-[#E2DED8] bg-[#F8F7F4] text-[13px]"
                               value="{{ $jaDefaultEnd }}" autocomplete="off" placeholder="YYYY-MM-DD">
                    </div>

                    {{-- Actions --}}
                    <div class="flex w-full flex-wrap items-center gap-2.5 pt-1">
                        <button type="button" id="apply-filters"
                            class="inline-flex items-center gap-2 rounded-[9px] bg-[#2563EB] px-5 py-2.5 text-[13px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none">
                            <i class="fa fa-check"></i> @lang('app.apply')
                        </button>
                        <button type="button" id="reset-filters"
                            class="inline-flex items-center gap-2 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-4 py-2.5 text-[13px] font-semibold text-[#8892A0] transition hover:border-[#EF4444] hover:text-[#EF4444] focus:outline-none">
                            <i class="fa fa-refresh"></i> @lang('app.reset')
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="jc-table-card table-wrapper ra-dt-wrap mt-4 w-full overflow-hidden rounded-[12px] border border-[#E8E6E1] bg-white shadow-sm">
            <table id="myTable" class="jc-cat-table display w-full" style="width:100%">
                <thead>
                    <tr>
                        <th style="width:48px;">
                            <input type="checkbox" id="chkCheckAll">
                        </th>
                        <th>#</th>
                        <th>@lang('modules.jobApplication.applicantName')</th>
                        <th>@lang('menu.jobs')</th>
                        <th>@lang('menu.locations')</th>
                        <th>@lang('app.status')</th>
                        <th>@lang('app.action')</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/bootstrap-datepicker/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>

    <script>
        var jaDefaultStart = @json($jaDefaultStart);
        var jaDefaultEnd   = @json($jaDefaultEnd);

        // ── Datepickers ────────────────────────────────────────────────
        $('#start-date').datepicker({ format: 'yyyy-mm-dd', autoclose: true });
        $('#end-date').datepicker({ format: 'yyyy-mm-dd', autoclose: true });

        $('#start-date').datepicker().on('changeDate', function () {
            $('#end-date').datepicker('setStartDate', $(this).datepicker('getDate'));
            jaTableSyncFilterBadge();
        });
        $('#end-date').datepicker().on('changeDate', function () {
            $('#start-date').datepicker('setEndDate', $(this).datepicker('getDate'));
            jaTableSyncFilterBadge();
        });

        // ── Select2 ────────────────────────────────────────────────────
        $('#filter-form select.select2').select2({ width: '100%' });

        // ── Question value toggle ──────────────────────────────────────
        $('#question_value').addClass('hidden');
        $('#questions').on('change', function () {
            if ($(this).val() === 'all') {
                $('#question_value').addClass('hidden');
                $('#question-value').val('');
            } else {
                $('#question_value').removeClass('hidden');
            }
        });

        // ── Filter badge counter ───────────────────────────────────────
        $('#filter-form').on('change', 'select', jaTableSyncFilterBadge);

        function jaTableSyncFilterBadge() {
            var n = 0;
            if (($('#company').val()   || 'all') !== 'all') n++;
            if (($('#jobs').val()      || 'all') !== 'all') n++;
            if (($('#location').val()  || 'all') !== 'all') n++;
            if (($('#questions').val() || 'all') !== 'all') n++;
            var sd = $('#start-date').val(), ed = $('#end-date').val();
            if (sd && sd !== jaDefaultStart) n++;
            if (ed && ed !== jaDefaultEnd)   n++;
            var $b = $('#ja-table-filter-active-count');
            $b.text(n).toggleClass('show', n > 0);
        }

        // ── Filter toggle bar ──────────────────────────────────────────
        $('.toggle-filter').on('click', function () {
            var $bar = $('#ja-table-filter-bar');
            $bar.toggleClass('ja-filter-open');
            $('#toggle-filter').toggleClass('ja-filter-btn-active', $bar.hasClass('ja-filter-open'));
        });

        // ── DataTable ──────────────────────────────────────────────────
        var table;

        tableLoad();
        jaTableSyncFilterBadge();

        $('#apply-filters').on('click', function () {
            tableLoad();
            jaTableSyncFilterBadge();
        });

        $('#reset-filters').on('click', function () {
            window.location.reload();
        });

        function tableLoad() {
            var skill          = $('#skill').val();
            var company        = $('#company').val();
            var jobs           = $('#jobs').val();
            var location       = $('#location').val();
            var questions      = $('#questions').val();
            var question_value = $('#question-value').val();
            var startDate      = $('#start-date').val() || 0;
            var endDate        = $('#end-date').val()   || 0;

            table = $('#myTable').DataTable({
                responsive: false,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{!! route('admin.applications-archive.data') !!}",
                    data: function (d) {
                        return $.extend({}, d, {
                            skill:          skill,
                            company:        company,
                            jobs:           jobs,
                            location:       location,
                            questions:      questions,
                            question_value: question_value,
                            start_date:     startDate,
                            end_date:       endDate
                        });
                    }
                },
                language: languageOptions(),
                stripeClasses: [],
                dom: '<"jc-table-toolbar"lf>rt<"jc-table-toolbar jc-table-toolbar--footer"ip>',
                drawCallback: function () {
                    $('[data-toggle="tooltip"]').tooltip();
                },
                columns: [
                            {
                                data: 'select_orders',
                                name: 'select_orders',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'DT_Row_Index',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'full_name',
                                name: 'full_name',
                                width: '17%'
                            },
                            {
                                data: 'title',
                                name: 'job_id',
                                width: '17%'
                            },
                            {
                                data: 'location',
                                name: 'location_id'
                            },
                            {
                                data: 'status',
                                name: 'status_id'
                            },
                            {
                                data: 'action',
                                name: 'action',
                                searchable: false,
                                orderable: false,
                                width: '15%'
                            }
                        ]
            });
        }

        // ── Checkbox / bulk delete ─────────────────────────────────────
        $("#deleteAllSelectedRecords").hide();

        $('#chkCheckAll').on('click', function () {
            $('.checkBoxClass').prop('checked', $(this).prop('checked'));
            var checked = $('input[name="check[]"]').length;
            (checked > 0 && this.checked)
                ? $('#deleteAllSelectedRecords').show()
                : $('#deleteAllSelectedRecords').hide();
        });

        $(document).on('change', 'input[name="check[]"]', function () {
            var n = $('input[name="check[]"]:checked').length;
            n > 0 ? $('#deleteAllSelectedRecords').show() : $('#deleteAllSelectedRecords').hide();
        });

        $('#deleteAllSelectedRecords').on('click', function (e) {
            e.preventDefault();
            var rowdIds = $("#myTable input:checkbox:checked").map(function () {
                return $(this).val();
            }).get();

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
            }, function (isConfirm) {
                if (isConfirm) {
                    var url = "{{ route('admin.applications-archive.deleteRecords', ':rowdIds') }}";
                    url = url.replace(':rowdIds', rowdIds);
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': "{{ csrf_token() }}" },
                        success: function (response) {
                            if (response.status === 'success') {
                                $.unblockUI();
                                table.draw(false);
                            }
                        }
                    });
                }
            });
        });

        // ── Show detail sidebar ────────────────────────────────────────
        $('#myTable').on('click', '.show-detail', function () {
            var $sidebar = $("#right-sidebar");
            $sidebar.removeClass('translate-x-full').addClass('shw-rside');
            var id  = $(this).data('row-id');
            var url = "{{ route('admin.applications-archive.show', ':id') }}".replace(':id', id);
            $.easyAjax({
                type: 'GET', url: url,
                success: function (response) {
                    if (response.status === 'success') {
                        $('#right-sidebar-content').html(response.view);
                    }
                }
            });
        });

        // ── Export ────────────────────────────────────────────────────
        function exportJobApplication() {
            var skill     = $('#skill').val()     || undefined;
            var company   = $('#company').val()   || 'all';
            var jobs      = $('#jobs').val()      || 'all';
            var location  = $('#location').val()  || 'all';
            var startDate = $('#start-date').val() || 0;
            var endDate   = $('#end-date').val()   || 0;

            var url = '{{ route('admin.applications-archive.export', ':skill') }}';
            url = url.replace(':skill', skill || 'all');

            url += '?company=' + company + '&jobs=' + jobs + '&location=' + location
                 + '&start_date=' + startDate + '&end_date=' + endDate;

            window.location.href = url;
        }

        // ── Company → Jobs cascade ─────────────────────────────────────
        $('#company').on('change', function () {
            var company_id = $(this).val();
            $.ajax({
                url: "{{ route('admin.job-applications.get-jobs') }}",
                type: 'GET',
                data: { companyId: company_id },
                success: function (data) {
                    var was = $('#jobs').val();
                    $('#jobs').select2('destroy').html(data.jobs).select2({ width: '100%' });
                    var val = $('#jobs option[value="' + was + '"]').length ? was : 'all';
                    $('#jobs').val(val).trigger('change');
                }
            });
        });
    </script>
@endpush