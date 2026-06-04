@extends('layouts.app')

@php
    $jaDefaultStart = now()->subDays(30)->format('Y-m-d');
    $jaDefaultEnd   = now()->format('Y-m-d');
    $jaStagesJson   = $boardColumns->map(fn($c) => [
        'id'    => $c->id,
        'slug'  => $c->status,
        'color' => $c->color ?? '#2563eb',
        'label' => ucfirst($c->status),
    ])->values()->toJson();

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

        /* ── Stage tabs ── */
        .ja-stage-tab {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 13px 16px;
            border: none;
            background: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 600;
            color: #64748b;
            border-bottom: 2.5px solid transparent;
            white-space: nowrap;
            transition: color .15s;
        }
        .ja-stage-tab:hover { color: #1A1E2E; }
        .ja-stage-tab.active { color: var(--tab-color, #2563eb); border-bottom-color: var(--tab-color, #2563eb); }
        .ja-stage-tab.active .ja-stage-badge { background: var(--tab-color, #2563eb); color: #fff; }
        .ja-stage-badge {
            background: #F1F3F7;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            min-width: 20px;
            text-align: center;
            transition: background .15s, color .15s;
        }
        .ja-ko-tab { margin-left: auto; border-left: 1px solid #E8E6E1; padding-left: 4px; }
        .ja-ko-tab .ja-stage-tab { color: #94a3b8; }
        .ja-ko-tab .ja-stage-tab:hover { color: #dc2626; }
        .ja-ko-tab .ja-stage-tab.active { color: #dc2626; border-bottom-color: #dc2626; }
        .ja-ko-tab .ja-stage-tab.active .ja-stage-badge { background: #dc2626; color: #fff; }
        .ja-ko-tab .ja-stage-badge { background: #fee2e2; color: #dc2626; }

        /* ── Filter bar ── */
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

        /* ── Table row actions ── */
        .ja-row-actions { display: flex; gap: 5px; justify-content: flex-end; align-items: center; }
        .ja-act-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            border: 1.5px solid #E2DED8;
            border-radius: 8px;
            background: #fff;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            color: #5A6478;
            font-family: inherit;
            transition: background .12s, border-color .12s, color .12s;
        }
        .ja-act-btn:hover { background: #F1F3F7; border-color: #C4CBD4; }
        .ja-act-btn.move { background: #2563eb; color: #fff; border-color: #2563eb; }
        .ja-act-btn.move:hover { background: #1d4ed8; }
        .ja-act-btn.reject { color: #dc2626; border-color: #fecaca; background: #fef2f2; }
        .ja-act-btn.reject:hover { background: #fee2e2; }
        .ja-act-btn.restore { color: #0369a1; border-color: #bae6fd; background: #e0f2fe; }
        .ja-act-btn.restore:hover { background: #bae6fd; }
        .ja-act-btn.icon-only { padding: 5px 8px; }

        /* ── Bulk bar ── */
        #ja-bulk-bar {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: #0f172a;
            color: #fff;
            border-radius: 14px;
            padding: 10px 16px;
            display: none;
            align-items: center;
            gap: 12px;
            box-shadow: 0 12px 40px rgba(15,23,42,.35);
            z-index: 999;
            white-space: nowrap;
            min-width: 400px;
        }
        #ja-bulk-bar.show { display: flex; }
        .ja-bulk-cnt {
            background: #2563eb;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }
        .ja-bulk-sep { width: 1px; height: 24px; background: #334155; flex-shrink: 0; }
        .ja-bulk-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border: none;
            border-radius: 9px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .ja-bulk-btn.blue { background: #2563eb; color: #fff; }
        .ja-bulk-btn.blue:hover { background: #1d4ed8; }
        .ja-bulk-btn.red { background: #dc2626; color: #fff; }
        .ja-bulk-btn.red:hover { background: #b91c1c; }
        .ja-bulk-btn.teal { background: #0ea5e9; color: #fff; }
        .ja-bulk-btn.clear { background: none; color: #94a3b8; font-size: 13px; }
        .ja-bulk-btn.clear:hover { color: #fff; }

        /* ── KO banner ── */
        #ja-ko-banner {
            margin: 12px 0 0;
            padding: 10px 16px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            font-size: 12.5px;
            color: #991b1b;
            display: none;
            align-items: center;
            gap: 8px;
        }
        #ja-ko-banner.show { display: flex; }

        /* ── Checkbox ── */
        .ja-chk {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1.5px solid #C4CBD4;
            background: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .12s;
        }
        .ja-chk.on { background: #2563eb; border-color: #2563eb; }
        .ja-chk input { display: none; }
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

        {{-- ── Top toolbar ── --}}
        <div class="mb-0 flex flex-shrink-0 flex-wrap items-center gap-2.5">
            {{-- View toggle --}}
            <div class="inline-flex gap-0.5 rounded-[10px] bg-[#F1F3F7] p-0.5">
                <a href="{{ route('admin.job-applications.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border-0 bg-transparent px-3.5 py-1.5 text-[12.5px] font-semibold text-[#8892A0] transition hover:text-[#1A1E2E]">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    @lang('modules.jobApplication.boardView')
                </a>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-1.5 text-[12.5px] font-semibold text-[#1A1E2E] shadow-[0_1px_4px_rgba(0,0,0,0.08)]">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    @lang('app.tableView')
                </span>
            </div>

            {{-- Filter toggle --}}
            <button type="button" id="toggle-filter" class="toggle-filter inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB] focus:outline-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                @lang('app.filterResults')
                <span class="ja-filter-active-count" id="ja-table-filter-active-count">0</span>
            </button>

            {{-- Mail settings --}}
            <a href="#" class="mail_setting inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB]">
                <i class="fa fa-envelope-o"></i>
                @lang('modules.applicationSetting.mailSettings')
            </a>

            {{-- Export --}}
            <button type="button" onclick="exportJobApplication()" class="inline-flex items-center gap-1.5 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-3.5 py-[7px] text-[12.5px] font-semibold text-[#5A6478] transition hover:border-[#2563EB] hover:bg-[#EFF6FF] hover:text-[#2563EB]">
                <i class="fa fa-upload"></i>
                @lang('menu.export')
            </button>

            @include('admin.job-applications.partials.ai-compare-modal')
        </div>

        {{-- ── Filter bar (collapsible — same as original) ── --}}
        <div id="ja-table-filter-bar" class="border-b border-[#E8E6E1] bg-white mt-3">
            <div class="px-5 sm:px-6">
                <div class="flex items-center justify-between gap-2 pb-3 pt-0.5">
                    <h4 class="text-[13px] font-bold text-[#1A1E2E]">@lang('app.filterBy')</h4>
                    <button type="button" class="toggle-filter flex h-8 w-8 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-[#EEF0F5]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="filter-form" class="flex flex-wrap items-end gap-3.5 pb-3">

                    <div class="flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.status')</label>
                        <select class="select2 w-full" name="status" id="status">
                            <option value="all">@lang('modules.jobApplication.allStatus')</option>
                            @forelse($boardColumns as $col)
                                <option value="{{ $col->id }}">{{ ucfirst($col->status) }}</option>
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
                        <button type="button" id="apply-filters" class="inline-flex items-center gap-2 rounded-[9px] bg-[#2563EB] px-5 py-2.5 text-[13px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none">
                            <i class="fa fa-check"></i> @lang('app.apply')
                        </button>
                        <button type="button" id="reset-filters" class="inline-flex items-center gap-2 rounded-[9px] border-[1.5px] border-[#E2DED8] bg-white px-4 py-2.5 text-[13px] font-semibold text-[#8892A0] transition hover:border-[#EF4444] hover:text-[#EF4444] focus:outline-none">
                            <i class="fa fa-refresh"></i> @lang('app.reset')
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Stage tabs ── --}}
        <div class="flex bg-white border-b border-[#E8E6E1] overflow-x-auto mt-3 rounded-t-[12px]" id="ja-stage-tabs-row">
            @forelse($boardColumns as $col)
            <button
                class="ja-stage-tab"
                data-stage-id="{{ $col->id }}"
                data-stage-color="{{ $col->color ?? '#2563eb' }}"
                data-stage-slug="{{ $col->status }}"
                onclick="jaStageTab({{ $col->id }}, '{{ addslashes($col->color ?? '#2563eb') }}', '{{ addslashes($col->status) }}')"
                style="--tab-color: {{ $col->color ?? '#2563eb' }}">
                {{ ucfirst($col->status) }}
                <span class="ja-stage-badge" id="ja-tab-count-{{ $col->id }}">0</span>
            </button>
            @empty
            @endforelse
            {{-- Knockouts tab --}}
            <div class="ja-ko-tab flex items-center">
                <button class="ja-stage-tab" id="ja-ko-tab-btn" onclick="jaKOTab()">
                    <i class="fa fa-user-times"></i>
                    Knockouts
                    <span class="ja-stage-badge" id="ja-ko-tab-count">0</span>
                </button>
            </div>
        </div>

        {{-- KO banner --}}
        <div id="ja-ko-banner">
            <i class="fa fa-info-circle"></i>
            These applicants were knocked out by screening rules or manually. They are hidden from the main pipeline. You can restore anyone back to the queue.
        </div>

        {{-- ── Table ── --}}
        <div class="jc-table-card table-wrapper ra-dt-wrap w-full overflow-hidden rounded-b-[12px] border border-t-0 border-[#E8E6E1] bg-white shadow-sm">
            <table id="myTable" class="jc-cat-table display w-full" style="width:100%">
                <thead>
                <tr>
                    <th style="width:40px;"><div class="ja-chk" id="ja-chk-all" onclick="jaToggleAll()" title="Select all"></div></th>
                    <th>@lang('modules.jobApplication.applicantName')</th>
                    <th>@lang('menu.jobs')</th>
                    <th>@lang('menu.locations')</th>
                    <th>@lang('app.status')</th>
                    <th class="jc-th-right" style="padding-right:16px;">@lang('app.action')</th>
                </tr>
                </thead>
            </table>
        </div>

    </div>
</div>

{{-- ── Bulk action bar ── --}}
<div id="ja-bulk-bar">
    <div class="ja-bulk-cnt" id="ja-bulk-cnt">0</div>
    <span style="font-size:13.5px;font-weight:600;">selected</span>
    <div class="ja-bulk-sep"></div>
    <div id="ja-bulk-actions"></div>
    <div class="ja-bulk-sep"></div>
    <button class="ja-bulk-btn clear" onclick="jaClearSelection()">
        <i class="fa fa-times"></i> Clear
    </button>
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
                <button type="button" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-[#F1F3F7]" onclick="$('#scheduleDetailModal').addClass('hidden')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="modal-body max-h-[min(60vh,420px)] overflow-y-auto p-6 text-[13px] text-[#1A1E2E]">Loading...</div>
            <div class="flex items-center justify-end gap-2.5 border-t border-[#F0EEE9] px-6 py-3.5">
                <button type="button" class="rounded-[9px] bg-[#F1F3F7] px-5 py-2.5 text-[13px] font-semibold text-[#5A6478]" onclick="$('#scheduleDetailModal').addClass('hidden')">@lang('app.close')</button>
                <button type="button" class="rounded-[9px] bg-[#2563EB] px-6 py-2.5 text-[13px] font-bold text-white">@lang('app.save')</button>
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
var jaStages = {!! $jaStagesJson !!};

    // ── State ────────────────────────────────────────────────────
    var jaActiveStageId = 'all';  // 'all' | stage id
    var jaShowKO        = false;
    var jaSelectedIds   = new Set();
    var table;

    // ── Helpers ──────────────────────────────────────────────────
   function jaNextStage(currentSlug) {
        var order = jaStages.map(function(s){ return s.slug; });
        var idx = order.indexOf(currentSlug);
        if (idx === -1 || idx >= order.length - 1) return null;
        var nextSlug = order[idx + 1];
        return jaStages.find(function(s){ return s.slug === nextSlug; }) || null;
    }

    // ── Tab switching ────────────────────────────────────────────
    function jaStageTab(stageId, color, slug) {
        jaShowKO = false;
        jaActiveStageId = stageId;
        jaSelectedIds = new Set();

        // Update tab styles
        document.querySelectorAll('.ja-stage-tab').forEach(function(el){
            el.classList.remove('active');
        });
        var btn = document.querySelector('[data-stage-id="' + stageId + '"]');
        if (btn) { btn.classList.add('active'); btn.style.setProperty('--tab-color', color); }

        document.getElementById('ja-ko-tab-btn').classList.remove('active');
        document.getElementById('ja-ko-banner').classList.remove('show');

        // Sync the hidden status select and reload
        $('#status').val(stageId).trigger('change.select2');
        tableLoad('filter');
        jaRenderBulkBar();
        jaTableSyncFilterBadge();
    }

    function jaKOTab() {
        jaShowKO = true;
        jaActiveStageId = 'all';
        jaSelectedIds = new Set();

        document.querySelectorAll('.ja-stage-tab').forEach(function(el){
            el.classList.remove('active');
        });
        document.getElementById('ja-ko-tab-btn').classList.add('active');
        document.getElementById('ja-ko-banner').classList.add('show');

        $('#status').val('all').trigger('change.select2');
        tableLoad('knockout');
        jaRenderBulkBar();
    }

    // ── Selection ────────────────────────────────────────────────
    function jaToggleRow(id, el) {
        if (jaSelectedIds.has(id)) {
            jaSelectedIds.delete(id);
            el.classList.remove('on');
            el.innerHTML = '';
        } else {
            jaSelectedIds.add(id);
            el.classList.add('on');
            el.innerHTML = '<i class="fa fa-check" style="font-size:10px;color:#fff"></i>';
        }
        jaRenderBulkBar();
        jaUpdateAllChk();
    }

    function jaToggleAll() {
        var allChk = document.getElementById('ja-chk-all');
        var rowChks = document.querySelectorAll('.ja-row-chk');
        var allOn = allChk.classList.contains('on');
        rowChks.forEach(function(el){
            var id = parseInt(el.dataset.id);
            if (allOn) {
                jaSelectedIds.delete(id);
                el.classList.remove('on');
                el.innerHTML = '';
            } else {
                jaSelectedIds.add(id);
                el.classList.add('on');
                el.innerHTML = '<i class="fa fa-check" style="font-size:10px;color:#fff"></i>';
            }
        });
        if (allOn) { allChk.classList.remove('on'); allChk.innerHTML = ''; }
        else { allChk.classList.add('on'); allChk.innerHTML = '<i class="fa fa-check" style="font-size:10px;color:#fff"></i>'; }
        jaRenderBulkBar();
    }

    function jaUpdateAllChk() {
        var rowChks = document.querySelectorAll('.ja-row-chk');
        var total = rowChks.length;
        var selCount = 0;
        rowChks.forEach(function(el){ if (el.classList.contains('on')) selCount++; });
        var allChk = document.getElementById('ja-chk-all');
        if (total > 0 && selCount === total) {
            allChk.classList.add('on');
            allChk.innerHTML = '<i class="fa fa-check" style="font-size:10px;color:#fff"></i>';
        } else {
            allChk.classList.remove('on');
            allChk.innerHTML = '';
        }
    }

    function jaClearSelection() {
        jaSelectedIds = new Set();
        document.querySelectorAll('.ja-row-chk').forEach(function(el){
            el.classList.remove('on');
            el.innerHTML = '';
        });
        var allChk = document.getElementById('ja-chk-all');
        allChk.classList.remove('on');
        allChk.innerHTML = '';
        jaRenderBulkBar();
    }

    // ── Bulk action bar ──────────────────────────────────────────
    function jaRenderBulkBar() {
        var n = jaSelectedIds.size;
        var bar = document.getElementById('ja-bulk-bar');
        document.getElementById('ja-bulk-cnt').textContent = n;
        if (!n) { bar.classList.remove('show'); return; }
        bar.classList.add('show');

        var actEl = document.getElementById('ja-bulk-actions');
        var html = '';

        if (jaShowKO) {
            html = '<button class="ja-bulk-btn teal" onclick="jaBulkRestore()"><i class="fa fa-undo"></i> Restore to queue</button>';
        } else {
            var currentStage = jaStages.find(function(s){ return s.id == jaActiveStageId; });
            if (currentStage) {
                var next = jaNextStage(currentStage.slug);
                if (next) {
                    html += '<button class="ja-bulk-btn blue" onclick="jaBulkMove(' + next.id + ')"><i class="fa fa-arrow-right"></i> Move to ' + next.label + '</button> ';
                }
                if (currentStage.slug !== 'rejected' && currentStage.slug !== 'hired') {
                    var rejStage = jaStages.find(function(s){ return s.slug === 'rejected'; });
                    if (rejStage) {
                        html += '<button class="ja-bulk-btn red" onclick="jaBulkMove(' + rejStage.id + ')"><i class="fa fa-times"></i> Reject all</button>';
                    }
                }
            } else {
                // 'all' stage — just reject option
                var rejStage = jaStages.find(function(s){ return s.slug === 'rejected'; });
                if (rejStage) {
                    html += '<button class="ja-bulk-btn red" onclick="jaBulkMove(' + rejStage.id + ')"><i class="fa fa-times"></i> Reject all</button>';
                }
            }
        }
        actEl.innerHTML = html;
    }

    // ── Bulk actions (AJAX) ──────────────────────────────────────
    function jaBulkMove(toStatusId) {
        if (!jaSelectedIds.size) return;
        var ids = Array.from(jaSelectedIds);
        $.easyAjax({
            url: '{{ route("admin.job-applications.bulk-status-update") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', ids: ids, status_id: toStatusId },
            success: function(res) {
                if (res.status === 'success') {
                    jaClearSelection();
                    table.draw(false);
                    jaLoadTabCounts();
                }
            }
        });
    }

    function jaBulkRestore() {
        if (!jaSelectedIds.size) return;
        var ids = Array.from(jaSelectedIds);
        $.easyAjax({
            url: '{{ route("admin.job-applications.bulk-restore-knockout") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', ids: ids },
            success: function(res) {
                if (res.status === 'success') {
                    jaClearSelection();
                    table.draw(false);
                    jaLoadTabCounts();
                }
            }
        });
    }


    function jaMoveOne(appId, toStatusId) {
        $.easyAjax({
            url: '{{ route("admin.job-applications.bulk-status-update") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', ids: [appId], status_id: toStatusId },
            success: function(res) {
                if (res.status === 'success') {
                    jaSelectedIds.delete(appId);
                    table.draw(false);
                    jaLoadTabCounts();
                    jaRenderBulkBar();
                }
            }
        });
    }


    function jaMoveOneBySlug(appId, toSlug) {
        var stage = jaStages.find(function(s){ return s.slug === toSlug; });
        if (!stage) {
            alert('Stage not found: ' + toSlug);
            return;
        }
        jaMoveOne(appId, stage.id);
    }
    function jaRestoreOne(appId) {
        $.easyAjax({
            url: '{{ route("admin.job-applications.bulk-restore-knockout") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', ids: [appId] },
            success: function(res) {
                if (res.status === 'success') {
                    jaSelectedIds.delete(appId);
                    table.draw(false);
                    jaLoadTabCounts();
                    jaRenderBulkBar();
                }
            }
        });
    }

    // ── Tab counts ───────────────────────────────────────────────
    function jaLoadTabCounts() {
        $.ajax({
            url: '{{ route("admin.job-applications.stage-counts") }}',
            type: 'GET',
            success: function(res) {
                if (!res.counts) return;
                $.each(res.counts, function(stageId, cnt) {
                    $('#ja-tab-count-' + stageId).text(cnt);
                });
                $('#ja-ko-tab-count').text(res.ko_count || 0);
            }
        });
    }

    // ── DataTable load ───────────────────────────────────────────
    function tableLoad(type) {
        var status         = $('#status').val();
        var jobs           = $('#jobs').val();
        var questions      = $('#questions').val();
        var location       = $('#location').val();
        var company        = $('#company').val();
        var question_value = $('#question-value').val();
        var knockout       = jaShowKO ? 1 : 0;

        table = $('#myTable').DataTable({
            responsive: false,
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: '{!! route('admin.job-applications.data') !!}?status=' + status
                + '&location=' + location
                + '&jobs=' + jobs
                + '&questions=' + questions
                + '&question_value=' + question_value
                + '&company=' + company
                + '&knockout=' + knockout,
            language: languageOptions(),
            stripeClasses: [],
            dom: '<"jc-table-toolbar"lf>rt<"jc-table-toolbar jc-table-toolbar--footer"ip>',
            drawCallback: function() {
                if (typeof $.fn.tooltip === 'function') {
                    $('[data-toggle="tooltip"]').tooltip();
                }
                jaUpdateAllChk();
            },
            order: [[1, 'asc']],
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    width: '40px',
                    render: function(data, type, row) {
                        return '<div class="ja-chk ja-row-chk" data-id="' + row.id + '" onclick="jaToggleRow(' + row.id + ', this)"></div>';
                    }
                },
                { data: 'full_name',    name: 'full_name',  width: '17%' },
                { data: 'title',        name: 'job_id',     width: '15%' },
                { data: 'location_id',  name: 'location_id' },
                { data: 'status',       name: 'status_id' },
                { data: 'action',       name: 'action', width: '18%', searchable: false, className: 'jc-td-right' }
            ]
        });
    }

    // ── Filter badge counter (original logic preserved) ──────────
    $('#filter-form').on('change', 'select', function() { jaTableSyncFilterBadge(); });

    function jaTableSyncFilterBadge() {
        var n = 0;
        if (($('#status').val()    || 'all') !== 'all') n++;
        if (($('#company').val()   || 'all') !== 'all') n++;
        if (($('#jobs').val()      || 'all') !== 'all') n++;
        if (($('#location').val()  || 'all') !== 'all') n++;
        if (($('#questions').val() || 'all') !== 'all') n++;
        var $b = $('#ja-table-filter-active-count');
        $b.text(n).toggleClass('show', n > 0);
    }

    // ── Filter toggle (original behaviour) ──────────────────────
    $('.toggle-filter').on('click', function() {
        var $bar = $('#ja-table-filter-bar');
        $bar.toggleClass('ja-filter-open');
        $('#toggle-filter').toggleClass('ja-filter-btn-active', $bar.hasClass('ja-filter-open'));
    });

    // ── Question value toggle ────────────────────────────────────
    $('#question_value').addClass('hidden');
    $('#questions').on('change', function() {
        if ($(this).val() === 'all') {
            $('#question_value').addClass('hidden');
            $('#question-value').val('');
        } else {
            $('#question_value').removeClass('hidden');
        }
    });

    // ── Apply / Reset filters ────────────────────────────────────
    $('#apply-filters').on('click', function() {
        jaSelectedIds = new Set();
        jaRenderBulkBar();
        tableLoad('filter');
        jaTableSyncFilterBadge();
    });

    $('#reset-filters').on('click', function() {
        window.location.reload();
    });

    // ── Show detail sidebar ──────────────────────────────────────
    $('#myTable').on('click', '.show-detail', function() {
        var $sidebar  = $('#right-sidebar');
        var $backdrop = $('#right-sidebar-backdrop');
        $sidebar.removeClass('translate-x-full').addClass('translate-x-0');
        $backdrop.removeClass('hidden').css({ display: 'block', visibility: 'visible' });

        var id  = $(this).data('row-id');
        var url = "{{ route('admin.job-applications.show',':id') }}".replace(':id', id);
        $.easyAjax({
            type: 'GET', url: url,
            success: function(response) {
                if (response.status === 'success') {
                    $('#right-sidebar-content').html(response.view);
                }
            }
        });
    });

    // ── Delete / archive (original) ──────────────────────────────
    $('body').on('click', '.sa-params,.delete-document', function() {
        var id = $(this).data('row-id');
        var deleteDocClassPresent  = $(this).hasClass('delete-document');
        var saParamsClassPresent   = $(this).hasClass('sa-params');

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
        }, function(isConfirm) {
            if (isConfirm) {
                var url = '';
                if (deleteDocClassPresent) url = "{{ route('admin.documents.destroy',':id') }}";
                if (saParamsClassPresent)  url = "{{ route('admin.job-applications.destroy',':id') }}";
                url = url.replace(':id', id);
                $.easyAjax({
                    type: 'POST', url: url,
                    data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                    success: function(response) {
                        if (response.status === 'success') {
                            $.unblockUI();
                            if (deleteDocClassPresent && typeof docTable !== 'undefined') docTable.draw(false);
                            if (saParamsClassPresent  && typeof table      !== 'undefined') table.draw(false);
                            jaLoadTabCounts();
                        }
                    }
                });
            }
        });
    });

    // ── Export (original) ────────────────────────────────────────
    function exportJobApplication() {
        var startDate = 0, endDate = 0;
        var status   = $('#status').val();
        var jobs     = $('#jobs').val();
        var location = $('#location').val();
        var url = '{{ route('admin.job-applications.export', [':status',':location',':startDate',':endDate',':jobs']) }}';
        url = url.replace(':status', status).replace(':location', location)
                 .replace(':startDate', startDate).replace(':endDate', endDate)
                 .replace(':jobs', jobs);
        window.location.href = url;
    }

    // ── Create schedule (original) ───────────────────────────────
    function createSchedule(id) {
        var url = "{{ route('admin.job-applications.create-schedule',':id') }}".replace(':id', id);
        $('#modelHeading').html('Schedule');
        $.ajaxModal('#scheduleDetailModal', url);
    }

    // ── Mail settings (original) ─────────────────────────────────
    $(document).on('click', '.mail_setting', function() {
        $.ajax({
            url: "{{ route('admin.application-setting.create') }}",
            success: function(data) {
                var data1 = eval(data.mail_setting);
                var options = '';
                $.each(data1, function(name, status) {
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

    // ── Company → Jobs cascade (original) ───────────────────────
    $('#company').on('change', function() {
        var company_id = $(this).val();
        $.ajax({
            url: "{{ route('admin.job-applications.get-jobs') }}",
            type: 'GET',
            data: { companyId: company_id },
            success: function(data) {
                var was = $('#jobs').val();
                $('#jobs').select2('destroy').html(data.jobs).select2({ width: '100%' });
                $('#jobs').val($('#jobs option[value="' + was + '"]').length ? was : 'all').trigger('change');
            }
        });
    });

    // ── Select2 init ─────────────────────────────────────────────
    $('#filter-form select.select2').not('#skill').select2({ width: '100%' });

    // ── Init ─────────────────────────────────────────────────────
    tableLoad('load');
    jaLoadTabCounts();
    jaTableSyncFilterBadge();
    </script>
@endpush