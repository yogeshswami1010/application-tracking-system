@extends('layouts.app')

@section('hide-ra-page-header')
@endsection

@push('head-script')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/plugins/jQueryUI/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/lobipanel/dist/css/lobipanel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/multiselect/css/multi-select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/jquery-bar-rating-master/dist/themes/fontawesome-stars.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/colorpicker/bootstrap-colorpicker.min.css') }}">
    @include('admin.job-applications.partials.select2-filter-skin')

    <style>
        .datepicker { z-index: 9999 !important; }
        .ja-board-scope { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        .job-apps-kanban-scroll::-webkit-scrollbar { height: 5px; }
        .job-apps-kanban-scroll::-webkit-scrollbar-thumb { background: #d1d9e8; border-radius: 4px; }
        .panel-scroll::-webkit-scrollbar { width: 3px; }
        .panel-scroll::-webkit-scrollbar-thumb { background: #c4cbd4; border-radius: 3px; }
        #modal-data-application { animation: ja-mpi 0.28s cubic-bezier(0.22, 1, 0.36, 1); }
        @keyframes ja-mpi { from { opacity: 0; transform: scale(0.94) translateY(8px); } to { opacity: 1; transform: scale(1) translateY(0); } }

        /* ── Enhanced filter bar ── */
        .ja-filter-card {
            background: #fff;
            border: 1.5px solid #E8E6E1;
            border-radius: 14px;
            padding: 14px 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.045);
        }
        .ja-filter-row {
            display: flex;
            gap: 10px;
            flex-wrap: nowrap;
        }
        @media (max-width: 900px) {
            .ja-filter-row { flex-wrap: wrap; }
        }
        .ja-filter-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            min-width: 130px;
        }
        .ja-filter-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #9CA3AF;
            white-space: nowrap;
        }
        /* Override select2 to match pill style */
        .ja-filter-card .select2-container--default .select2-selection--single {
            height: 36px !important;
            border: 1.5px solid #E2DED8 !important;
            border-radius: 9px !important;
            background: #F8F7F5 !important;
            display: flex;
            align-items: center;
            transition: border-color 0.15s, background 0.15s;
        }
        .ja-filter-card .select2-container--default .select2-selection--single:hover,
        .ja-filter-card .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #2563EB !important;
            background: #fff !important;
        }
        .ja-filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1A1E2E !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 34px !important;
            padding-left: 11px !important;
            padding-right: 28px !important;
        }
        .ja-filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px !important;
            right: 8px !important;
        }
        .ja-filter-card .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #9CA3AF transparent transparent !important;
        }
        /* Job total badge inline */
        .ja-job-badge {
            display: none;
            align-items: center;
            gap: 4px;
            padding: 3px 9px 3px 7px;
            border-radius: 999px;
            background: #EFF6FF;
            border: 1.5px solid #BFDBFE;
            font-size: 11px;
            font-weight: 700;
            color: #2563EB;
            white-space: nowrap;
            margin-top: 2px;
        }
        .ja-job-badge.show { display: inline-flex; }
        /* Action buttons */
        .ja-btn-apply {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 18px;
            border-radius: 9px;
            background: #2563EB;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            border: none;
            box-shadow: 0 2px 8px rgba(37,99,235,0.22);
            transition: background 0.15s, box-shadow 0.15s;
            white-space: nowrap;
            cursor: pointer;
        }
        .ja-btn-apply:hover { background: #1d4ed8; box-shadow: 0 4px 14px rgba(37,99,235,0.35); }
        .ja-btn-reset {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 14px;
            border-radius: 9px;
            background: #F1F3F7;
            color: #6B7280;
            font-size: 13px;
            font-weight: 600;
            border: 1.5px solid #E2DED8;
            transition: border-color 0.15s, color 0.15s, background 0.15s;
            white-space: nowrap;
            cursor: pointer;
        }
        .ja-btn-reset:hover { border-color: #EF4444; color: #EF4444; background: #FFF5F5; }
        .ja-filter-divider {
            width: 1px;
            height: 28px;
            background: #E8E6E1;
            margin-bottom: 4px;
            flex-shrink: 0;
        }
        .ja-filter-actions {
            display: flex;
            align-items: flex-end;
            gap: 7px;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('page-title-html')
<div class="mb-4 flex flex-shrink-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-[22px] font-bold leading-tight tracking-[-0.025em] text-[#1A1E2E]">
            @lang('modules.front.job')
            <span class="ml-1 font-[family-name:Instrument_Serif] text-[20px] font-normal italic text-[#2563EB]">@lang('modules.jobApplication.applicationsWord')</span>
        </h1>
    </div>
</div>
@endsection

@section('page-subtitle')
<p class="mt-0.5 text-[12.5px] text-[#8892A0]">@lang('modules.jobApplication.boardPipelineHint')</p>
@endsection

@if(in_array('add_job_applications', $userPermissions))
@section('create-button')
<a href="{{ route('admin.job-applications.create') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
    @lang('app.createNew')
</a>
@endsection
@endif

@php
    $totalBoardApps = $boardColumns->sum('application_count');
@endphp

@section('content')

    <div class="ja-board-scope -mx-4 -mt-2 flex min-h-[calc(100dvh-9.5rem)] flex-col bg-[#EEF0F5] sm:-mx-6">
        <div class="flex min-h-0 flex-1 flex-col overflow-hidden px-5 pb-5 pt-5 sm:px-6">

            {{-- Top toolbar --}}
            <div class="mb-[18px] flex flex-shrink-0 flex-wrap items-center gap-2.5">
                <div class="inline-flex gap-0.5 rounded-[10px] bg-[#F1F3F7] p-0.5">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-1.5 text-[12.5px] font-semibold text-[#1A1E2E] shadow-[0_1px_4px_rgba(0,0,0,0.08)]">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                        @lang('modules.jobApplication.boardView')
                    </span>
                    <a href="{{ route('admin.job-applications.table') }}" class="inline-flex items-center gap-1.5 rounded-lg border-0 bg-transparent px-3.5 py-1.5 text-[12.5px] font-semibold text-[#8892A0] transition hover:text-[#1A1E2E]">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        @lang('app.tableView')
                    </a>
                </div>

                <div class="ml-2 flex flex-wrap items-center gap-2">
                    <span class="text-[12px] text-[#8892A0]">@lang('app.total')</span>
                    <span id="ja-board-total" class="text-[12.5px] font-bold text-[#1A1E2E] tabular-nums">{{ $totalBoardApps }}</span>
                    <span class="text-[12px] text-[#B0B8C4]">@lang('modules.jobApplication.applicationsLower')</span>
                </div>

                <a href="#" class="mail_setting inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB]">
                    <i class="fa fa-envelope-o"></i>
                    @lang('modules.applicationSetting.mailSettings')
                </a>
                <button type="button" onclick="exportJobApplication()" class="inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB]">
                    <i class="fa fa-upload"></i>
                    @lang('menu.export')
                </button>
                @include('admin.job-applications.partials.ai-compare-modal')
                <a href="javascript:createApplicationStatus();" class="inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-dashed border-[#C4CBD4] bg-transparent px-3.5 py-[7px] text-[12.5px] font-semibold text-[#8892A0] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB]">
                    <i class="fa fa-bookmark-o"></i>
                    @lang('modules.jobApplication.newStatus')
                </a>

                <div class="ml-auto flex min-w-[min(100%,220px)] flex-1 items-center gap-2 rounded-[10px] border-[1.5px] border-[#E2DED8] bg-white py-[7px] pl-3.5 pr-3.5 transition focus-within:border-[#2563EB] sm:max-w-[260px] sm:flex-none">
                    <svg class="h-3.5 w-3.5 shrink-0 text-[#9CA3AF]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input id="search" type="search" name="search" placeholder="@lang('modules.jobApplication.searchNameOrPosition')" class="min-w-0 flex-1 border-0 bg-transparent text-[13px] text-[#1A1E2E] outline-none placeholder:text-[#B0B8C4]">
                </div>
            </div>

            {{-- Always-visible filter bar --}}
            <div class="ja-filter-card">
            <div class="ja-filter-row">

                {{-- Filter icon + label --}}
                <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;margin-bottom:4px;">
                    <svg style="width:15px;height:15px;color:#2563EB;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span style="font-size:12px;font-weight:700;color:#1A1E2E;white-space:nowrap;">
                        @lang('app.filterBy')
                    </span>
                </div>

                <div class="ja-filter-divider"></div>

                {{-- Company --}}
                <div class="ja-filter-field">
                    <span class="ja-filter-label">@lang('app.company')</span>
                    <select class="select2 w-full" name="company" id="company">
                        <option value="all">@lang('modules.jobApplication.allCompany')</option>
                        @forelse($companies as $company)
                            <option title="{{ ucfirst($company->company_name) }}" value="{{ $company->id }}">
                                {{ ucfirst($company->company_name) }}
                            </option>
                        @empty
                        @endforelse
                    </select>
                </div>

                {{-- Jobs --}}
                <div class="ja-filter-field">

                    {{-- Label + Badge (RIGHT SIDE) --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                        <span class="ja-filter-label" style="margin:0;">
                            @lang('menu.jobs')
                        </span>

                        <span id="ja-job-total-badge" class="ja-job-badge">
                            <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                            </svg>
                            <span id="ja-job-total-count">0</span>&nbsp; Total Applicant
                        </span>
                    </div>

                    {{-- Jobs Select --}}
                    <select class="select2 w-full" name="jobs" id="jobs">
                        <option value="all">@lang('modules.jobApplication.allJobs')</option>
                        @forelse($jobs as $job)
                            <option title="{{ ucfirst($job->title) }}" value="{{ $job->id }}">
                                {{ ucfirst($job->title) }}
                            </option>
                        @empty
                        @endforelse
                    </select>

                </div>

                {{-- Location --}}
                <div class="ja-filter-field">
                    <span class="ja-filter-label">@lang('menu.locations')</span>
                    <select class="select2 w-full" name="location" id="location">
                        <option value="all">@lang('modules.jobApplication.allLocation')</option>
                        @forelse($locations as $location)
                            <option value="{{ $location->id }}">{{ ucfirst($location->location) }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>

                {{-- Custom Question --}}
                <div class="ja-filter-field">
                    <span class="ja-filter-label">@lang('modules.jobApplication.allQuestion')</span>
                    <select class="select2 w-full" name="question" id="questions">
                        <option value="all">@lang('modules.jobApplication.allQuestion')</option>
                        @forelse($questions as $question)
                            <option value="{{ $question->id }}">
                                {{ ucfirst($question->question) }}
                            </option>
                        @empty
                        @endforelse
                    </select>
                </div>

                {{-- Question value (conditional) --}}
                <div class="ja-filter-field" id="question_value" style="display:none;">
                    <span class="ja-filter-label">@lang('app.filterBy')</span>
                    <input type="text"
                        style="height:36px;border:1.5px solid #E2DED8;border-radius:9px;background:#F8F7F5;font-size:13px;padding:0 11px;outline:none;transition:border-color .15s;width:100%;"
                        name="question_value"
                        id="question-value"
                        placeholder="">
                </div>

                <div class="ja-filter-divider"></div>

                {{-- Action buttons --}}
                <div class="ja-filter-actions">
                    <button type="button" id="apply-filters" class="ja-btn-apply">
                        <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        @lang('app.apply')
                    </button>

                    <button type="button" id="reset-filters" class="ja-btn-reset">
                        <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        @lang('app.reset')
                    </button>
                </div>

            </div>
        </div>

            <div class="container-scroll flex min-h-0 flex-1 flex-col overflow-hidden pt-1">
                <div class="container-row job-apps-kanban-scroll flex min-h-[min(480px,calc(100dvh-19rem))] flex-1 gap-3.5 overflow-x-auto overflow-y-hidden pb-2">
                </div>
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
                    <button type="button" id="job-application-modal-save" class="rounded-[9px] bg-[#2563EB] px-6 py-2.5 text-[13px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8] hover:shadow-[0_4px_14px_rgba(37,99,235,0.38)]">@lang('app.save')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/plugins/jQueryUI/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/lobipanel/dist/js/lobipanel.min.js') }}"></script>
    <script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/multiselect/js/jquery.multi-select.js') }}"></script>
    <script src="{{ asset('assets/node_modules_files/bootstrap-datepicker/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/bootstrap-select/bootstrap-select.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/jquery-bar-rating-master/dist/jquery.barrating.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/colorpicker/bootstrap-colorpicker.min.js') }}"></script>

    <script>
        $(".select2").select2({ width: '100%' });

        loadData();

        // Show question value input only when a specific question is selected
        $('#question_value').hide();
        $('#questions').on('change', function () {
            if ($(this).val() === 'all') {
                $('#question_value').hide();
                $('#question-value').val('');
            } else {
                $('#question_value').show();
            }
        });

        // Show total applied count when a specific job is selected
        // Use a flag to prevent the badge AJAX from firing during a programmatic reset
        var jaJobChangeEnabled = true;
        $('#jobs').on('change', function () {
            if (!jaJobChangeEnabled) return;
            var jobId = $(this).val();
            if (jobId && jobId !== 'all') {
                var url = '{{ route('admin.job-applications.index') }}?startDate=&endDate=&jobs=' + jobId + '&search=&location=all&skill=&questions=all&question_value=&company=all';
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        var $tmp = $('<div>').html(response.view);
                        var total = 0;
                        $tmp.find('[id^="columnCount_"]').each(function () {
                            total += parseInt($(this).text(), 10) || 0;
                        });
                        if (total === 0 && response.total !== undefined) {
                            total = parseInt(response.total, 10) || 0;
                        }
                        $('#ja-job-total-count').text(total);
                        $('#ja-job-total-badge').addClass('show');
                    }
                });
            } else {
                $('#ja-job-total-badge').removeClass('show');
            }
        });

        // Apply filters
        $('#apply-filters').on('click', function () {
            loadData();
        });

        // Reset filters — suppress badge AJAX, restore original jobs list, then reload
        $('#reset-filters').on('click', function () {
            window.location.reload();
        });

        // Search
        search($('#search'), 500, 'data');

        // Load board columns
        function loadData() {
            var jobs          = $('#jobs').val();
            var location      = $('#location').val();
            var questions     = $('#questions').val();
            var search        = $('#search').val();
            var question_value = $('#question-value').val();
            var company       = $('#company').val();

            var url = '{{ route('admin.job-applications.index') }}?startDate=&endDate=&jobs=' + jobs + '&search=' + search + '&location=' + location + '&skill=&questions=' + questions + '&question_value=' + question_value + '&company=' + company;

            $.easyAjax({
                url: url,
                container: '.container-row',
                type: 'GET',
                success: function (response) {
                    $('.container-row').html(response.view);
                    var sum = 0;
                    $('[id^="columnCount_"]').each(function () {
                        sum += parseInt($(this).text(), 10) || 0;
                    });
                    $('#ja-board-total').text(sum);
                }
            });
        }

        // Load more cards inside a column
        $('body').on('click', '.load-more-application', function () {
            var columnId         = $(this).data('column-id');
            var totalTasks       = $(this).data('total-tasks');
            var currentTotalTasks = $('#drag-container-' + columnId + ' .task-card').length;
            var lastelement      = $('#drag-container-' + columnId + ' div.task-card:last');

            var jobs           = $('#jobs').val();
            var location       = $('#location').val();
            var questions      = $('#questions').val();
            var search         = $('#search').val();
            var question_value = $('#question-value').val();
            var company        = $('#company').val();

            var url = '{{ route('admin.job-applications.loadMore') }}?startDate=&endDate=&jobs=' + jobs + '&search=' + search + '&location=' + location + '&skill=&questions=' + questions + '&question_value=' + question_value + '&columnId=' + columnId + '&currentTotalRecords=' + currentTotalTasks + '&totalRecord=' + totalTasks + '&company=' + company;

            $.easyAjax({
                url: url,
                container: '#drag-container-' + columnId,
                blockUI: true,
                type: 'GET',
                success: function (response) {
                    lastelement.after(response.view);

                    if (response.load_more === 'show') {
                        $('#loadMoreBox' + columnId).removeClass('hidden');
                    } else {
                        $('#loadMoreBox' + columnId).remove();
                    }

                    $('body').tooltip({ selector: '[data-toggle="tooltip"]' });

                    $('.example-fontawesome').barrating({ theme: 'fontawesome-stars', showSelectedRating: false, readonly: true });
                    $('.bar-rating').each(function () {
                        $(this).barrating('set', $(this).data('value') || '');
                    });

                    // Re-init drag-and-drop after load more
                    var isDragging = 0;
                    var currentApplicationId = 0;
                    var $sortableContainers = $('.lobipanel-parent-sortable');
                    $sortableContainers.each(function () {
                        if ($(this).hasClass('ui-sortable')) $(this).sortable('destroy');
                    });
                    initSortable();
                }
            });
        });

        function initSortable() {
            var isDragging = 0;
            var currentApplicationId = 0;

            $('.lobipanel-parent-sortable').sortable({
                items: '.task-card',
                connectWith: '.lobipanel-parent-sortable',
                tolerance: 'pointer',
                helper: 'clone',
                distance: 8,
                placeholder: 'ui-sortable-placeholder',
                start: function (event, ui) {
                    isDragging = 1;
                    ui.item.data('old-column-id', ui.item.closest('.board-column').data('column-id'));
                    $('.board-column .panel-scroll').css('overflow-y', 'unset');
                },
                stop: function (event, ui) {
                    var oldCol             = parseInt(ui.item.data('old-column-id'), 10) || 0;
                    var newCol             = parseInt(ui.item.closest('.board-column').data('column-id'), 10) || 0;
                    var movedApplicationId = parseInt(ui.item.data('application-id'), 10) || 0;
                    currentApplicationId   = movedApplicationId;
                    ui.item.attr('data-column-id', newCol);

                    var boardColumnIds = [], applicationIds = [], prioritys = [];
                    ui.item.parent().children('.show-detail').each(function (ind, el) {
                        boardColumnIds.push($(el).closest('.board-column').data('column-id'));
                        applicationIds.push($(el).data('application-id'));
                        prioritys.push($(el).index());
                    });

                    if (oldCol == 3 && newCol == 4) {
                        $('#buttonBox' + oldCol + movedApplicationId).removeClass('hidden')
                            .html('<button onclick="sendReminder(' + movedApplicationId + ', \'notify\')" type="button" class="px-2 py-1 text-xs bg-cyan-600 text-white rounded hover:bg-cyan-700 notify-button">@lang('app.notify')</button>')
                            .attr('id', 'buttonBox' + newCol + movedApplicationId);
                    } else if (oldCol == 4 && newCol == 3) {
                        var ts = $('#buttonBox' + oldCol + movedApplicationId).data('timestamp');
                        var cd = {{ $currentDate }};
                        if (cd < ts) {
                            $('#buttonBox' + oldCol + movedApplicationId).removeClass('hidden')
                                .html('<button onclick="sendReminder(' + movedApplicationId + ', \'reminder\')" type="button" class="px-2 py-1 text-xs bg-cyan-600 text-white rounded hover:bg-cyan-700 notify-button">@lang('app.reminder')</button>');
                        }
                        $('#buttonBox' + oldCol + movedApplicationId).attr('id', 'buttonBox' + newCol + movedApplicationId);
                    } else {
                        $('#buttonBox' + oldCol + movedApplicationId).addClass('hidden').attr('id', 'buttonBox' + newCol + movedApplicationId);
                    }

                    $.easyAjax({
                        url: '{{ route("admin.job-applications.updateIndex") }}',
                        type: 'POST',
                        container: '.container-row',
                        data: {
                            boardColumnIds, applicationIds, prioritys,
                            startDate: null, endDate: null,
                            jobs: $('#jobs').val(), search: $('#search').val(),
                            draggingTaskId: movedApplicationId,
                            draggedTaskId: movedApplicationId,
                            '_token': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (movedApplicationId !== 0) {
                                $.each(response.columnCountByIds, function (key, value) {
                                    $('#columnCount_' + value.id).html(value.status_count);
                                });
                            }
                        }
                    });

                    $('.board-column .panel-scroll').css('overflow-y', 'auto');
                    setTimeout(function () { isDragging = 0; }, 50);
                }
            }).disableSelection();

            $('.show-detail').off('click.ja').on('click.ja', function (event) {
                if ($(event.target).hasClass('notify-button')) return false;
                var id = $(this).data('application-id');
                currentApplicationId = id;

                if (isDragging == 0) {
                    if (typeof window.jaDisposeApplicantProfile === 'function') window.jaDisposeApplicantProfile();
                    var $sidebar = $('#right-sidebar');
                    var $backdrop = $('#right-sidebar-backdrop');
                    $sidebar.removeClass('translate-x-full').addClass('translate-x-0');
                    $backdrop.removeClass('hidden');

                    var url = "{{ route('admin.job-applications.show',':id') }}".replace(':id', id);
                    var requestId = (window._jaDirectProfileRequestId || 0) + 1;
                    window._jaDirectProfileRequestId = requestId;
                    if (window._jaDirectProfileXhr && window._jaDirectProfileXhr.readyState !== 4) {
                        window._jaDirectProfileXhr.abort();
                    }
                    window._jaDirectProfileXhr = $.ajax({
                        type: 'GET', url: url,
                        success: function (response) {
                            if (requestId === window._jaDirectProfileRequestId && response.status == 'success') { $('#right-sidebar').removeClass('translate-x-full').addClass('translate-x-0'); $('#right-sidebar-backdrop').removeClass('hidden').css('display', 'block'); $('#right-sidebar-content').html(response.view); }
                        }
                    });
                }
            });
        }

        function createSchedule(id) {
            var url = "{{ route('admin.job-applications.create-schedule',':id') }}".replace(':id', id);
            $('#modelHeading').html('Schedule');
            $('#job-application-modal-save').addClass('save-schedule');
            $.ajaxModal('#scheduleDetailModal', url);
        }

        $(document).off('click.jobApplicationSchedule', '.save-schedule')
            .on('click.jobApplicationSchedule', '.save-schedule', function (event) {
                event.preventDefault();
                $.easyAjax({
                    url: '{{ route('admin.job-applications.store-schedule') }}',
                    container: '#scheduleDetailModal',
                    type: 'POST',
                    data: $('#createSchedule').serialize(),
                    redirect: false,
                    disableButton: true,
                    buttonSelector: '.save-schedule',
                    success: function (response) {
                        if (response.status === 'success') window.location.reload();
                    }
                });
            });

        function createApplicationStatus() {
            $('#modelHeading').html('Application Status');
            $('#job-application-modal-save').removeClass('save-schedule');
            $.ajaxModal('#scheduleDetailModal', "{{ route('admin.application-status.create') }}");
        }

        function deleteStatus(id) {
            var applicationIds = [];
            $('.board-column[data-column-id="' + id + '"]').find('.lobipanel.show-detail').each(function () {
                applicationIds.push($(this).data('application-id'));
            });

            swal({
                title: "@lang('errors.areYouSure')",
                text: "@lang('errors.deleteStatusWarning')",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "@lang('app.delete')",
                cancelButtonText: "@lang('app.cancel')",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {
                    var url = "{{ route('admin.application-status.destroy', ':id') }}".replace(':id', id);
                    $.easyAjax({
                        url, type: 'POST', container: '.container-row',
                        data: { _token: '{{ csrf_token() }}', _method: 'DELETE', applicationIds },
                        success: function (response) {
                            if (response.status == 'success') loadData();
                        }
                    });
                }
            });
        }

        function editStatus(id) {
            var url = "{{ route('admin.application-status.edit', ':id') }}".replace(':id', id);
            $('#modelHeading').html('Application Status');
            $('#job-application-modal-save').removeClass('save-schedule');
            $.ajaxModal('#scheduleDetailModal', url);
        }

        function saveStatus() {
            $.easyAjax({
                url: "{{ route('admin.application-status.store') }}",
                container: '#createStatus',
                type: 'POST',
                data: $('#createStatus').serialize(),
                success: function () {
                    $('#scheduleDetailModal').addClass('hidden');
                    loadData();
                }
            });
        }

        function updateStatus(id) {
            var url = "{{ route('admin.application-status.update', ':id') }}".replace(':id', id);
            $.easyAjax({
                url, container: '#updateStatus', type: 'POST',
                data: $('#updateStatus').serialize(),
                success: function () {
                    $('#scheduleDetailModal').addClass('hidden');
                    loadData();
                }
            });
        }

        function exportJobApplication() {
            var jobs     = $('#jobs').val() || 'all';
            var location = $('#location').val() || 'all';
            var url = '{{ route('admin.job-applications.export', [':status',':location', ':startDate', ':endDate', ':jobs']) }}';
            url = url.replace(':status', 'all').replace(':location', location).replace(':startDate', 0).replace(':endDate', 0).replace(':jobs', jobs);
            window.location.href = url;
        }

        $(document).on('click', '.mail_setting', function () {
            $.ajax({
                url: "{{ route('admin.application-setting.create') }}",
                success: function (data) {
                    var data1 = eval(data.mail_setting);
                    var options = '';
                    $.each(data1, function (name, status) {
                        var checked = status.status == true ? 'checked' : '';
                        options += '<input type="checkbox" ' + checked + ' style="text-align:center;margin:6px 15px 13px 0;" name="checkBoardColumn[]" id="checkbox-' + name + '" value="' + name + '" />';
                        options += '<label for="checkbox-' + name + '" style="text-align:center;margin:6px 15px 13px 0;">' + status.name + '</label>';
                    });
                    $('#assetNameMenu').html(options);
                    $('#legal_term').val(data.legal_term);
                    $('#ModalLoginForm').removeClass('hidden');
                }
            });
            return false;
        });

        $('#company').on('change', function () {
            jaJobChangeEnabled = false;
            $.ajax({
                url: "{{ route('admin.job-applications.get-jobs') }}",
                type: 'GET',
                data: { companyId: $(this).val() },
                success: function (data) {
                    $('#jobs').select2('destroy').html(data.jobs).select2({ width: '100%' });
                    $('#ja-job-total-badge').removeClass('show');
                    jaJobChangeEnabled = true;
                },
                error: function () {
                    jaJobChangeEnabled = true;
                }
            });
        });
    </script>
@endpush
