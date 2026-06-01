@extends('layouts.app')

@php
    $jaDefaultStart = now()->subDays(30)->format('Y-m-d');
    $jaDefaultEnd = now()->format('Y-m-d');
@endphp

@push('head-script')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/html5-editor/bootstrap-wysihtml5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/multiselect/css/multi-select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/iCheck/all.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    @include('admin.job-applications.partials.select2-filter-skin')

    <style>
        .mb-20 { margin-bottom: 20px; }
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
        .hidden {
                display: none !important;
                visibility: hidden !important;
            }
    </style>
@endpush

@if(in_array("add_job_applications", $userPermissions))
@section('create-button')
    <a href="{{ route('admin.job-applications.create') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        @lang('app.createNew')
    </a>
@endsection
@endif

@section('content')
    <div class="ja-board-scope -mx-4 -mt-2 flex min-h-[calc(100dvh-9.5rem)] flex-col bg-[#EEF0F5] sm:-mx-6">
        <div class="flex min-h-0 flex-1 flex-col overflow-hidden px-5 pb-5 pt-5 sm:px-6">
            <div class="mb-[18px] flex flex-shrink-0 flex-wrap items-center gap-2.5">
                <div class="inline-flex gap-0.5 rounded-[10px] bg-[#F1F3F7] p-0.5">
                    <a href="{{ route('admin.job-applications.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border-0 bg-transparent px-3.5 py-1.5 text-[12.5px] font-semibold text-[#8892A0] transition hover:text-[#1A1E2E]">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                        @lang('modules.jobApplication.boardView')
                    </a>
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-1.5 text-[12.5px] font-semibold text-[#1A1E2E] shadow-[0_1px_4px_rgba(0,0,0,0.08)]">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        @lang('app.tableView')
                    </span>
                </div>

                <button type="button" id="toggle-filter" class="toggle-filter inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB] focus:outline-none">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    @lang('app.filterResults')
                    <span class="ja-filter-active-count" id="ja-table-filter-active-count">0</span>
                </button>

                <a href="#" class="mail_setting inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB]">
                    <i class="fa fa-envelope-o"></i>
                    @lang('modules.applicationSetting.mailSettings')
                </a>
                <button type="button" onclick="exportJobApplication()" class="inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB]">
                    <i class="fa fa-upload"></i>
                    @lang('menu.export')
                </button>
                @include('admin.job-applications.partials.ai-compare-modal')
            </div>

            <div id="ja-table-filter-bar" class="border-b border-[#E8E6E1] bg-white">
                <div class="px-5 sm:px-6">
                    <div class="flex items-center justify-between gap-2 pb-3 pt-0.5">
                        <h4 class="text-[13px] font-bold text-[#1A1E2E]">@lang('app.filterBy')</h4>
                        <button type="button" class="toggle-filter flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-[#EEF0F5]" aria-label="@lang('app.close')">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form id="filter-form" class="flex flex-wrap items-end gap-3.5 pb-3">

                        <div class="flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]">
                            <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.status')</label>
                            <select class="select2 w-full" name="status" id="status">
                                <option value="all">@lang('modules.jobApplication.allStatus')</option>
                                @forelse($boardColumns as $status)
                                    <option value="{{ $status->id }}">{{ ucfirst($status->status) }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>

                        <div class="flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]">
                            <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.company')</label>
                            <select class="select2 w-full" name="company" id="company">
                                <option value="all">@lang('modules.jobApplication.allCompany')</option>
                                @forelse($companies as $company)
                                    <option title="{{ ucfirst($company->company_name) }}" value="{{ $company->id }}">{{ ucfirst($company->company_name) }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>

                        <div class="flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]">
                            <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('menu.jobs')</label>
                            <select class="select2 w-full" name="jobs" id="jobs">
                                <option value="all">@lang('modules.jobApplication.allJobs')</option>
                                @forelse($jobs as $job)
                                    <option title="{{ ucfirst($job->title) }}" value="{{ $job->id }}">{{ ucfirst($job->title) }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>

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

                        <div class="hidden flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]" id="question_value">
                            <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.filterBy')</label>
                            <input type="text" class="form-control rounded-[10px] border-[1.5px] border-[#E2DED8] bg-[#F8F7F4] text-[13px]" name="question_value" id="question-value" placeholder="">
                        </div>

                        <div class="flex w-full flex-wrap items-center gap-2.5 pt-1">
                            <button type="button" id="apply-filters" class="inline-flex items-center gap-2 rounded-[9px] bg-[#2563EB] px-5 py-2.5 text-[13px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8] hover:shadow-[0_4px_14px_rgba(37,99,235,0.38)] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"><i class="fa fa-check"></i> @lang('app.apply')</button>
                            <button type="button" id="reset-filters" class="inline-flex items-center gap-2 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-4 py-2.5 text-[13px] font-semibold text-[#8892A0] transition hover:border-[#EF4444] hover:text-[#EF4444] focus:outline-none"><i class="fa fa-refresh"></i> @lang('app.reset')</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="jc-table-card table-wrapper ra-dt-wrap mt-4 w-full overflow-hidden rounded-[12px] border border-[#E8E6E1] bg-white shadow-sm">
                <table id="myTable" class="jc-cat-table display w-full" style="width:100%">
                    <thead>
                    <tr>
                        <th style="width:72px;">#</th>
                        <th>@lang('modules.jobApplication.applicantName')</th>
                        <th>@lang('menu.jobs')</th>
                        <th>@lang('menu.locations')</th>
                        <th>@lang('app.status')</th>
                        <th class="jc-th-right" style="padding-right:20px;">@lang('app.action')</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    @include('admin.application-setting.modal')
    <div class="hidden fixed inset-0 z-[210] flex items-center justify-center overflow-y-auto p-4" id="scheduleDetailModal" role="dialog" aria-labelledby="modelHeading" aria-hidden="true">
        <div class="fixed inset-0 bg-[rgba(15,31,61,0.5)] backdrop-blur-[3px] transition-opacity" onclick="$('#scheduleDetailModal').addClass('hidden')"></div>
        <div class="relative z-10 mx-auto w-full max-w-[min(94vw,640px)]">
            <div class="max-h-[min(90vh,720px)] overflow-hidden rounded-[20px] bg-white shadow-2xl" id="modal-data-application">
                <div class="flex items-start justify-between gap-3 border-b border-[#F0EEE9] px-6 pb-4 pt-5">
                    <div class="min-w-0">
                        <h3 class="text-[15.5px] font-bold leading-snug text-[#1A1E2E]" id="modelHeading"></h3>
                    </div>
                    <button type="button" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-[#F1F3F7]" onclick="$('#scheduleDetailModal').addClass('hidden')" aria-label="@lang('app.close')">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="modal-body max-h-[min(60vh,420px)] overflow-y-auto p-6 text-[13px] text-[#1A1E2E]">
                    Loading...
                </div>
                <div class="flex items-center justify-end gap-2.5 border-t border-[#F0EEE9] px-6 py-3.5">
                    <button type="button" class="rounded-[9px] bg-[#F1F3F7] px-5 py-2.5 text-[13px] font-semibold text-[#5A6478] transition hover:bg-[#E5E7ED]" onclick="$('#scheduleDetailModal').addClass('hidden')">@lang('app.close')</button>
                    <button type="button" class="rounded-[9px] bg-[#2563EB] px-6 py-2.5 text-[13px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8] hover:shadow-[0_4px_14px_rgba(37,99,235,0.38)]">@lang('app.save')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/bootstrap-select/bootstrap-select.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/multiselect/js/jquery.multi-select.js') }}"></script>
    <script src="{{ asset('assets/plugins/iCheck/icheck.min.js') }}"></script>
    <script src="{{ asset('assets/node_modules_files/bootstrap-datepicker/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>

    <script>
        var jaDefaultStart = @json($jaDefaultStart);
        var jaDefaultEnd = @json($jaDefaultEnd);
        var jaSkillsPlaceholder = @json(__('modules.jobApplication.selectSkillsPlaceholder'));

        $('#start-date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
        $('#end-date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        $('#question_value').addClass('hidden');
        $('#questions').on('change', function () {
            if ($(this).val() === 'all') {
                $('#question_value').addClass('hidden');
                $('#question-value').val('');
            } else {
                $('#question_value').removeClass('hidden');
            }
        });

        $('#start-date').datepicker().on('changeDate', function () {
            $('#end-date').datepicker('setStartDate', $(this).datepicker('getDate'));
        });
        $('#end-date').datepicker().on('changeDate', function () {
            $('#start-date').datepicker('setEndDate', $(this).datepicker('getDate'));
        });

        $('#filter-form select.select2').not('#skill').select2({
            width: '100%'
        });
        $('#skill').select2({
            width: '100%',
            placeholder: jaSkillsPlaceholder,
            allowClear: true
        });

        $('#filter-form').on('change', 'select', function () {
            jaTableSyncFilterBadge();
        });
        $('#start-date, #end-date').on('changeDate', function () {
            jaTableSyncFilterBadge();
        });
        function jaTableSyncFilterBadge() {
            var n = 0;

            if (($('#status').val() || 'all') !== 'all') n++;
            if (($('#company').val() || 'all') !== 'all') n++;
            if (($('#jobs').val() || 'all') !== 'all') n++;
            if (($('#location').val() || 'all') !== 'all') n++;
       
            if (($('#questions').val() || 'all') !== 'all') n++;
            var $b = $('#ja-table-filter-active-count');
            $b.text(n);
            $b.toggleClass('show', n > 0);
        }

        var table;
        tableLoad('load');
        jaTableSyncFilterBadge();

        $('#reset-filters').on('click', function () {
            window.location.reload();
        });

        $('#apply-filters').on('click', function () {
            tableLoad('filter');
            jaTableSyncFilterBadge();
        });

        function tableLoad(type) {
            var status = $('#status').val();
            var jobs = $('#jobs').val();
            var questions = $('#questions').val();
            var location = $('#location').val();
            var company = $('#company').val();
            var question_value = $('#question-value').val();

            table = $('#myTable').DataTable({
                responsive: false,
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: '{!! route('admin.job-applications.data') !!}?status=' + status + '&location=' + location + '&jobs=' + jobs + '&questions=' + questions + '&question_value=' + question_value + '&company=' + company,
                language: languageOptions(),
                stripeClasses: [],
                dom: '<"jc-table-toolbar"lf>rt<"jc-table-toolbar jc-table-toolbar--footer"ip>',
                drawCallback: function () {
                    if (typeof $.fn.tooltip === 'function') {
                        $('[data-toggle="tooltip"]').tooltip();
                    }
                },
                order: [[1, 'asc']],
                columns: [
                    { data: 'DT_Row_Index', orderable: false, searchable: false },
                    { data: 'full_name', name: 'full_name', width: '17%' },
                    { data: 'title', name: 'job_id', width: '17%' },
                    { data: 'location_id', name: 'location_id' },
                    { data: 'status', name: 'status_id' },
                    { data: 'action', name: 'action', width: '15%', searchable: false, className: 'jc-td-right' }
                ]
            });
        }

        $('body').on('click', '.sa-params,.delete-document', function () {
            var id = $(this).data('row-id');
            const deleteDocClassPresent = $(this).hasClass('delete-document');
            const saParamsClassPresent = $(this).hasClass('sa-params');

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
                    let url = '';

                    if (deleteDocClassPresent) {
                        url = "{{ route('admin.documents.destroy',':id') }}";
                    }
                    if (saParamsClassPresent) {
                        url = "{{ route('admin.job-applications.destroy',':id') }}";
                    }

                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': token, '_method': 'DELETE' },
                        success: function (response) {
                            if (response.status == "success") {
                                $.unblockUI();
                                if (deleteDocClassPresent && typeof docTable !== 'undefined') {
                                    docTable.draw(false);
                                }
                                if (saParamsClassPresent && typeof table !== 'undefined') {
                                    table.draw(false);
                                }
                            }
                        }
                    });
                }
            });
        });

        $('#myTable').on('click', '.show-detail', function () {
            var $sidebar = $("#right-sidebar");
            var $backdrop = $("#right-sidebar-backdrop");
            $sidebar.removeClass('translate-x-full').addClass('translate-x-0');
            $backdrop.removeClass('hidden').css({
                'display': 'block',
                'visibility': 'visible'
            });

            var id = $(this).data('row-id');
            var url = "{{ route('admin.job-applications.show',':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'GET',
                url: url,
                success: function (response) {
                    if (response.status == "success") {
                        $('#right-sidebar-content').html(response.view);
                    }
                }
            });
        });

        $('.toggle-filter').on('click', function () {
            var $bar = $('#ja-table-filter-bar');
            $bar.toggleClass('ja-filter-open');
            $('#toggle-filter').toggleClass('ja-filter-btn-active', $bar.hasClass('ja-filter-open'));
        });

        $('body').on('click', '.show-document', function () {
            const type = $(this).data('modal-name');
            const id = $(this).data('row-id');

            const url = "{{ route('admin.documents.index') }}?documentable_type=" + type + "&documentable_id=" + id;

            $.ajaxModal('#application-lg-modal', url);
        });

        function exportJobApplication() {
            var startDate;
            var endDate;
            var status = $('#status').val();
            var jobs = $('#jobs').val();
            var location = $('#location').val();

            startDate = $('#start-date').val();
            endDate = $('#end-date').val();

            if (startDate == '' || startDate == null) {
                startDate = 0;
            }

            if (endDate == '' || endDate == null) {
                endDate = 0;
            }

            var url = '{{ route('admin.job-applications.export', [':status',':location', ':startDate', ':endDate', ':jobs']) }}';
            url = url.replace(':status', status);
            url = url.replace(':location', location);
            url = url.replace(':startDate', startDate);
            url = url.replace(':endDate', endDate);
            url = url.replace(':jobs', jobs);

            window.location.href = url;
        }

        function createSchedule(id) {
            var url = "{{ route('admin.job-applications.create-schedule',':id') }}";
            url = url.replace(':id', id);
            $('#modelHeading').html('Schedule');
            $.ajaxModal('#scheduleDetailModal', url);
        }

        $(document).on('click', '.mail_setting', function () {
            var data1 = '';
            $.ajax({
                url: "{{ route('admin.application-setting.create') }}",
                success: function (data) {
                    data1 = eval(data.mail_setting);
                    var options = '';
                    $.each(data1, function (name, status) {
                        if (status.status == true) {
                            options += '<input type="checkbox"  checked style=text-align: center; margin: 6px 15px 13px 0px;" name="checkBoardColumn[]" id="checkbox-' + name + '" value="' + name + '"  />';
                            options += '<label for="checkbox-' + name + '" style="text-align: center; margin: 6px 15px 13px 0px;">' + status.name + '</label>';
                        } else {
                            options += '<input type="checkbox" style="text-align: center; margin: 6px 10px 4px 0px;" class = "iCheck-helper" name="checkBoardColumn[]" id="checkbox-' + name + '" value="' + name + '"  />';
                            options += '<label for="checkbox-' + name + '" style="text-align: center; margin: 6px 10px 4px 0px;">' + status.name + '</label>';
                        }
                    });
                    $('#assetNameMenu').html(options);
                    $('#legal_term').val(data.legal_term);
                    $('#ModalLoginForm').removeClass('hidden');
                    return false;
                }
            });
        });

        $('#company').on('change', function () {
            var company_id = $(this).val();
            $.ajax({
                url: "{{ route('admin.job-applications.get-jobs') }}",
                type: "GET",
                data: {
                    'companyId': company_id,
                },
                success: function (data) {
                    var was = $('#jobs').val();
                    $('#jobs').select2('destroy');
                    $('#jobs').html(data.jobs);
                    $('#jobs').select2({ width: '100%' });
                    if ($('#jobs option[value="' + was + '"]').length) {
                        $('#jobs').val(was).trigger('change');
                    } else {
                        $('#jobs').val('all').trigger('change');
                    }
                },
            });
        });
   
    </script>
@endpush
