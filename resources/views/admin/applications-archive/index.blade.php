@extends('layouts.app')

@push('head-script')
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/multiselect/css/multi-select.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    @include('admin.job-applications.partials.select2-filter-skin')
    <style>
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

        /* ── Skill tag input ─────────────────────────────────────────── */
        .skill-tag-wrap {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 5px;
            min-height: 38px;
            padding: 4px 10px;
            border: 1.5px solid #E2DED8;
            border-radius: 10px;
            background: #F8F7F4;
            cursor: text;
            transition: border-color .15s;
        }
        .skill-tag-wrap:focus-within {
            border-color: #2563eb;
            background: #fff;
        }
        .skill-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 9px;
            border-radius: 999px;
            background: #2563eb;
            color: #fff;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
        }
        .skill-tag .skill-tag-remove {
            cursor: pointer;
            font-size: 13px;
            line-height: 1;
            opacity: .75;
            transition: opacity .1s;
        }
        .skill-tag .skill-tag-remove:hover { opacity: 1; }
        .skill-tag-input {
            flex: 1;
            min-width: 90px;
            border: none !important;
            background: transparent !important;
            outline: none !important;
            box-shadow: none !important;
            font-size: 13px;
            padding: 0 !important;
            margin: 0 !important;
            height: 26px;
        }
        /* skill suggestion dropdown */
        .skill-suggestions {
            position: absolute;
            z-index: 9999;
            background: #fff;
            border: 1.5px solid #E2DED8;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,.10);
            max-height: 180px;
            overflow-y: auto;
            min-width: 180px;
        }
        .skill-suggestions .ss-item {
            padding: 7px 14px;
            font-size: 13px;
            cursor: pointer;
            color: #1A1E2E;
        }
        .skill-suggestions .ss-item:hover,
        .skill-suggestions .ss-item.active {
            background: #EFF6FF;
            color: #2563eb;
        }
        .skill-suggestions .ss-empty {
            padding: 7px 14px;
            font-size: 12px;
            color: #8892A0;
        }
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

            <a href="{{ route('admin.job-applications.create') }}"
                class="inline-flex items-center gap-1.5 rounded-[9px] bg-[#2563EB] px-3.5 py-2.5 text-[12.5px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none">
                <i class="fa fa-plus"></i> Create Applicant
            </a>
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

                    {{-- ── Skill tag input ── --}}
                    <!-- <div class="flex min-w-[200px] flex-1 flex-col gap-1 sm:max-w-[300px]">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">
                            @lang('modules.applicationArchive.enterSkill')
                        </label>
                        {{-- Hidden input carries comma-joined skill list for form submit --}}
                        <input type="hidden" id="skill" name="skill" value="">
                        <div class="relative">
                            <div class="skill-tag-wrap" id="skill-tag-wrap">
                                <input type="text" id="skill-tag-input" class="skill-tag-input"
                                    placeholder="Type a skill, press Enter…"
                                    autocomplete="off">
                            </div>
                            <div class="skill-suggestions" id="skill-suggestions" style="display:none;"></div>
                        </div>
                    </div> -->

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

                    {{-- Status --}}
                    <div class="flex min-w-[160px] flex-1 flex-col gap-1 sm:max-w-[220px]">
                        <label class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#8892A0]">@lang('app.status')</label>
                        <select class="select2 w-full" name="status" id="status">
                            <option value="all">@lang('modules.jobApplication.allStatus')</option>
                            @forelse($statuses as $s)
                                <option value="{{ $s->id }}">{{ ucfirst($s->status) }}</option>
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

                    {{-- Actions --}}
                    <div class="flex w-full flex-wrap items-center gap-2.5 pt-1">
                        
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
                        <th>#</th>
                        <th>@lang('modules.jobApplication.applicantName')</th>
                        <th>@lang('menu.jobs')</th>
                        <th>@lang('menu.locations')</th>
                        <th>@lang('app.status')</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}" type="text/javascript"></script>

    <script>
    // ── Skill tag input ────────────────────────────────────────────────────────
    (function () {
        // All available skills from Laravel (for autocomplete suggestions)
        var allSkills = @json($skills ?? []);   // expects an array of skill name strings

        var tags   = [];   // currently selected skill strings
        var active = -1;   // keyboard-highlighted suggestion index

        var $wrap  = $('#skill-tag-wrap');
        var $input = $('#skill-tag-input');
        var $drop  = $('#skill-suggestions');
        var $hidden= $('#skill');

        // ── helpers ──────────────────────────────────────────────────────────
        function syncHidden() {
            $hidden.val(tags.join(','));
            jaTableSyncFilterBadge();
            if (typeof jaApplyFilters === 'function') jaApplyFilters();
        }

        function renderTags() {
            $wrap.find('.skill-tag').remove();
            tags.forEach(function (tag, i) {
                var $t = $('<span class="skill-tag"></span>')
                    .text(tag)
                    .append(
                        $('<span class="skill-tag-remove">×</span>').on('click', function (e) {
                            e.stopPropagation();
                            tags.splice(i, 1);
                            renderTags();
                            syncHidden();
                        })
                    );
                $wrap.prepend($t); // prepend so input stays at end
            });
        }

        function addTag(val) {
            val = val.trim();
            if (!val) return;
            // Split on comma to allow pasting "React, Vue, Angular"
            val.split(',').forEach(function (v) {
                v = v.trim();
                if (v && !tags.includes(v)) {
                    tags.push(v);
                }
            });
            $input.val('');
            renderTags();
            syncHidden();
            hideDrop();
        }

        // ── suggestion dropdown ──────────────────────────────────────────────
        function showDrop(term) {
            if (!term) { hideDrop(); return; }
            var lower = term.toLowerCase();
            var matches = allSkills.filter(function (s) {
                return s.toLowerCase().includes(lower) && !tags.includes(s);
            }).slice(0, 8);

            if (!matches.length) {
                // Show "add custom" hint
                $drop.html('<div class="ss-item active" data-val="' + $('<div>').text(term).html() + '">'
                    + '+ Add "<strong>' + $('<div>').text(term).html() + '</strong>"'
                    + '</div>').show();
                active = 0;
                return;
            }

            active = -1;
            $drop.empty();
            matches.forEach(function (s) {
                $drop.append(
                    $('<div class="ss-item"></div>').text(s).attr('data-val', s)
                );
            });
            $drop.show();

            // Position below wrap
            positionDrop();
        }

        function positionDrop() {
            var offset = $wrap.offset();
            var height = $wrap.outerHeight();
            $drop.css({
                top:   offset.top + height + 4,
                left:  offset.left,
                width: $wrap.outerWidth()
            }).appendTo('body');
        }

        function hideDrop() { $drop.hide().empty(); active = -1; }

        // ── events ───────────────────────────────────────────────────────────
        $wrap.on('click', function () { $input.focus(); });

        $input.on('input', function () {
            showDrop($(this).val().trim());
        });

        $input.on('keydown', function (e) {
            var items = $drop.find('.ss-item');
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                if (active >= 0 && items.eq(active).length) {
                    addTag(items.eq(active).data('val'));
                } else {
                    addTag($(this).val());
                }
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                active = Math.min(active + 1, items.length - 1);
                items.removeClass('active').eq(active).addClass('active');
                return;
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                active = Math.max(active - 1, 0);
                items.removeClass('active').eq(active).addClass('active');
                return;
            }
            if (e.key === 'Backspace' && !$(this).val() && tags.length) {
                tags.pop();
                renderTags();
                syncHidden();
                hideDrop();
            }
            if (e.key === 'Escape') { hideDrop(); }
        });

        $drop.on('mousedown', '.ss-item', function (e) {
            e.preventDefault();
            addTag($(this).data('val'));
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#skill-tag-wrap, #skill-suggestions').length) {
                hideDrop();
            }
        });

        // reset helper exposed to global reset
        window.jaSkillReset = function () {
            tags = [];
            renderTags();
            syncHidden();
            hideDrop();
        };

        // read-back helper for tableLoad
        window.jaGetSkills = function () { return tags.join(','); };
    })();

    // ── Select2 ───────────────────────────────────────────────────────────────
    $('#filter-form select.select2').select2({ width: '100%' });

    // ── Question value toggle ─────────────────────────────────────────────────
    $('#question_value').addClass('hidden');
    $('#questions').on('change', function () {
        if ($(this).val() === 'all') {
            $('#question_value').addClass('hidden');
            $('#question-value').val('');
        } else {
            $('#question_value').removeClass('hidden');
        }
    });

  

    function jaTableSyncFilterBadge() {
        var n = 0;
        if (window.jaGetSkills && window.jaGetSkills())              n++;
        if (($('#company').val()   || 'all') !== 'all')              n++;
        if (($('#jobs').val()      || 'all') !== 'all')              n++;
        if (($('#location').val()  || 'all') !== 'all')              n++;
        if (($('#status').val()    || 'all') !== 'all')              n++;
        if (($('#questions').val() || 'all') !== 'all')              n++;
        var $b = $('#ja-table-filter-active-count');
        $b.text(n).toggleClass('show', n > 0);
    }

    // ── Filter toggle bar ─────────────────────────────────────────────────────
    $('.toggle-filter').on('click', function () {
        var $bar = $('#ja-table-filter-bar');
        $bar.toggleClass('ja-filter-open');
        $('#toggle-filter').toggleClass('ja-filter-btn-active', $bar.hasClass('ja-filter-open'));
    });

    // ── DataTable ─────────────────────────────────────────────────────────────
    var table;

    tableLoad();
    jaTableSyncFilterBadge();

    // Auto-apply on any filter change
    var jaFilterDebounce;
    function jaApplyFilters() {
        clearTimeout(jaFilterDebounce);
        jaFilterDebounce = setTimeout(function () {
            tableLoad();
            jaTableSyncFilterBadge();
        }, 250);
    }

    $('#filter-form').on('change', 'select', function () {
        jaApplyFilters();
    });

    $('#question-value').on('input', function () {
        jaApplyFilters();
    });

    $('#reset-filters').on('click', function () {
        window.location.reload();
    });

    function tableLoad() {
        var skill          = window.jaGetSkills ? window.jaGetSkills() : '';
        var company        = $('#company').val();
        var jobs           = $('#jobs').val();
        var location       = $('#location').val();
        var status         = $('#status').val();
        var questions      = $('#questions').val();
        var question_value = $('#question-value').val();

        // Destroy existing instance
        if ($.fn.DataTable.isDataTable('#myTable')) {
            $('#myTable').DataTable().destroy();
        }

        // Rebuild thead so DataTables has correct columns to measure
        $('#myTable').html(
            '<thead><tr>' +
                '<th>#</th>' +
                '<th>{{ __('modules.jobApplication.applicantName') }}</th>' +
                '<th>{{ __('menu.jobs') }}</th>' +
                '<th>{{ __('menu.locations') }}</th>' +
                '<th>{{ __('app.status') }}</th>' +
                '<th></th>' +
            '</tr></thead>' +
            '<tbody></tbody>'
        );

        table = $('#myTable').DataTable({
            responsive: false,
            serverSide: true,
            autoWidth: false,
            order: [[5, 'desc']],
            ajax: {
                url: "{!! route('admin.applications-archive.data') !!}",
                data: function (d) {
                    return $.extend({}, d, {
                        skill:          skill,
                        company:        company,
                        jobs:           jobs,
                        location:       location,
                        status:         status,
                        questions:      questions,
                        question_value: question_value
                    });
                }
            },
            language: languageOptions(),
            stripeClasses: [],
            dom: '<"jc-table-toolbar"lf>rt<"jc-table-toolbar jc-table-toolbar--footer"ip>',
            drawCallback: function () {
                if ($.fn.tooltip) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            },
            columns: [
                { data: 'DT_Row_Index', orderable: false,  searchable: false },
                { data: 'full_name',    name: 'full_name' },
                { data: 'title',        name: 'job_id' },
                { data: 'location',     name: 'location_id' },
                { data: 'status',       name: 'status_id',  orderable: false },
                { data: 'created_at',   name: 'created_at', visible: false, searchable: false }
            ]
        });
    }

    // ── Show detail sidebar ───────────────────────────────────────────────────
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

    // ── Export ────────────────────────────────────────────────────────────────
    function exportJobApplication() {
        var skill    = window.jaGetSkills ? window.jaGetSkills() : 'all';
        var company  = $('#company').val()  || 'all';
        var jobs     = $('#jobs').val()     || 'all';
        var location = $('#location').val() || 'all';
        var status   = $('#status').val()   || 'all';

        var url = '{{ route('admin.applications-archive.export', ':skill') }}';
        url = url.replace(':skill', skill || 'all');
        url += '?company=' + company + '&jobs=' + jobs
             + '&location=' + location + '&status=' + status;

        window.location.href = url;
    }

    // ── Company → Jobs cascade ────────────────────────────────────────────────
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