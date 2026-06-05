@extends('layouts.app')

@push('head-script')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datepicker/datepicker3.css') }}">
@endpush

@section('content')
<div class="flex flex-col">
    <div class="w-full">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">

                {{-- Page header --}}
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h4 class="text-xl font-semibold text-gray-900">@lang('app.createNew')</h4>
                        <p class="text-sm text-gray-500 mt-0.5">Upload one or multiple CVs — select each to review and save</p>
                    </div>
                    <span class="text-sm text-gray-400 bg-gray-100 px-3 py-1 rounded-full" id="bulk-progress-pill" style="display:none;">
                        <span id="bulk-done-count">0</span> of <span id="bulk-total-count">0</span> saved
                    </span>
                </div>

                {{-- Batch approve bar (shown once CVs are parsed) --}}
                <div id="bulk-batch-bar" style="display:none;" class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5 mb-5 text-sm text-blue-800">
                    <i class="fa fa-bolt"></i>
                    <span id="bulk-batch-msg" class="flex-1"></span>
                    <button type="button" id="bulk-approve-all"
                        class="px-3 py-1 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700">
                        Approve all to database
                    </button>
                </div>

                <div class="bulk-layout" id="bulk-layout">

                    {{-- ===== LEFT: CV queue ===== --}}
                    <div class="bulk-col-left" id="bulk-col-left">

                        {{-- Drop zone --}}
                        <div id="bulk-dropzone"
                            class="bulk-dropzone"
                            onclick="document.getElementById('bulk-file-input').click()"
                            ondragover="bulkOnDragOver(event)"
                            ondragleave="bulkOnDragLeave(event)"
                            ondrop="bulkOnDrop(event)">
                            <i class="fa fa-cloud-upload fa-lg mb-1 text-gray-400 block"></i>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                <strong class="text-blue-600">Click or drop CVs here</strong><br>
                                PDF, DOCX, TXT — multiple at once
                            </p>
                        </div>
                        <input type="file" id="bulk-file-input" multiple
                            accept=".pdf,.doc,.docx,.txt" style="display:none;">

                        {{-- Queue header --}}
                        <div class="flex items-center justify-between px-1">
                            <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Queue</span>
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium" id="bulk-q-count">0 files</span>
                        </div>

                        {{-- Queue empty state (lives outside the list so innerHTML never wipes it) --}}
                        <div id="bulk-q-empty" class="flex flex-col items-center justify-center gap-1 text-gray-400 py-6">
                            <i class="fa fa-files-o fa-2x"></i>
                            <span class="text-xs">No CVs uploaded yet</span>
                        </div>

                        {{-- Queue list --}}
                        <div class="bulk-queue-list" id="bulk-q-list" style="display:none;"></div>

                        {{-- Progress bar --}}
                        <div>
                            <div class="flex justify-between text-xs text-gray-400 mb-1">
                                <span id="bulk-prog-label">0 of 0 reviewed</span>
                                <span id="bulk-prog-pct">0%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1">
                                <div id="bulk-prog-fill" class="bg-blue-500 h-1 rounded-full transition-all" style="width:0%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== RIGHT: Review pane ===== --}}
                    <div class="bulk-col-right" id="bulk-col-right">

                        {{-- Empty state --}}
                        <div id="bulk-empty-state" class="bulk-empty-state">
                            <i class="fa fa-arrow-left fa-2x text-gray-300 mb-2"></i>
                            <p class="text-gray-400 text-sm">Upload CVs and select one from the queue to begin reviewing</p>
                        </div>

                        {{-- Review pane (hidden until a CV is selected) --}}
                        <div id="bulk-review-pane" style="display:none;" class="bulk-review-pane">

                            {{-- Pane header --}}
                            <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <div id="bulk-rev-avatar" class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm flex-shrink-0">?</div>
                                    <div>
                                        <p class="font-medium text-sm text-gray-900" id="bulk-rev-name">—</p>
                                        <p class="text-xs text-gray-400" id="bulk-rev-file">—</p>
                                    </div>
                                </div>
                                <span id="bulk-rev-flag" class="text-xs px-2 py-0.5 rounded-full font-medium"></span>
                            </div>

                            {{-- Split: CV viewer + form --}}
                            <div class="bulk-split">

                                {{-- CV Viewer --}}
                                <div class="bulk-cv-viewer" id="bulk-cv-viewer">
                                    <div class="flex flex-col items-center justify-center h-full gap-2 text-gray-400" id="bulk-cv-parsing">
                                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        <p class="text-xs">Parsing CV...</p>
                                    </div>
                                </div>

                                {{-- Form --}}
                                <div class="bulk-form-scroll">
                                    <form id="bulk-candidate-form" autocomplete="off">
                                        @csrf
                                        <input type="hidden" name="job_id"      id="bulk-job-id">
                                        <input type="hidden" name="location_id" id="bulk-location-id">
                                        <input type="hidden" id="bulk-resume-text-for-ai" value="">

                                        {{-- Name --}}
                                        <div class="bulk-fg">
                                            <label class="bulk-label">
                                                @lang('app.name') <span class="text-red-400">*</span>
                                                <span class="bulk-conf bulk-conf-hi" id="bconf-name">high</span>
                                            </label>
                                            <input type="text" name="full_name" id="bf-name" class="bulk-input bulk-parsed"
                                                placeholder="@lang('app.name')" onmouseover="bulkHl('name')" required>
                                        </div>

                                        {{-- Email --}}
                                        <div class="bulk-fg">
                                            <label class="bulk-label">
                                                @lang('app.email') <span class="text-red-400">*</span>
                                                <span class="bulk-conf" id="bconf-email">—</span>
                                            </label>
                                            <input type="email" name="email" id="bf-email" class="bulk-input bulk-parsed"
                                                placeholder="@lang('app.email')" onmouseover="bulkHl('email')" required>
                                        </div>

                                        {{-- Phone --}}
                                        <div class="bulk-fg">
                                            <label class="bulk-label">
                                                @lang('app.phone') <span class="text-red-400">*</span>
                                                <span class="bulk-conf" id="bconf-phone">—</span>
                                            </label>
                                            <input type="tel" name="phone" id="bf-phone" class="bulk-input bulk-parsed"
                                                placeholder="@lang('app.phone')" onmouseover="bulkHl('phone')" required>
                                        </div>

                                        {{-- Skills --}}
                                        <div class="bulk-fg">
                                            <label class="bulk-label">
                                                Skills
                                                <span class="bulk-conf bulk-conf-hi" id="bconf-skills">high</span>
                                            </label>
                                            <input type="text" name="skills" id="bf-skills" class="bulk-input bulk-parsed"
                                                placeholder="Skills from CV" onmouseover="bulkHl('skills')">
                                        </div>

                                        {{-- Address --}}
                                        <div class="bulk-fg">
                                            <label class="bulk-label">@lang('app.address')</label>
                                            <textarea name="address" id="bf-address" rows="2"
                                                class="bulk-input bulk-parsed resize-none"
                                                placeholder="@lang('app.address')"></textarea>
                                        </div>

                                        {{-- Notes --}}
                                        <div class="bulk-fg">
                                            <label class="bulk-label">@lang('modules.jobApplication.applicantNotes')</label>
                                            <div id="bulk-notes-list" class="space-y-1 mb-2"></div>
                                            <div class="flex gap-2">
                                                <textarea id="bulk-notes-input" rows="2"
                                                    class="bulk-input flex-1 resize-none text-xs"
                                                    placeholder="@lang('modules.jobApplication.addNote')"></textarea>
                                                <button type="button" id="bulk-add-note"
                                                    class="px-2 py-1 bg-blue-600 text-white rounded text-xs font-medium self-end whitespace-nowrap hover:bg-blue-700">
                                                    <i class="fa fa-plus mr-1"></i>Add
                                                </button>
                                            </div>
                                            <div id="bulk-notes-hidden"></div>
                                        </div>

                                        {{-- Questions section (loaded dynamically) --}}
                                        <div id="bulk-question-section" style="display:none;">
                                            <div class="border-t border-gray-100 pt-3 mt-2">
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">@lang('modules.front.additionalDetails')</p>
                                                <div id="bulk-question-box"></div>
                                            </div>
                                        </div>

                                        {{-- Required columns (country/state/city injected here) --}}
                                        <div id="bulk-show-columns"></div>
                                        <div id="bulk-show-sections"></div>

                                    </form>
                                </div>
                            </div>

                            {{-- Filing + actions bar --}}
                            <div class="bulk-filing-bar">

                                {{-- Entry type --}}
                                <div class="flex flex-col gap-1.5 flex-shrink-0">
                                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">File to</span>
                                    <div class="flex gap-1.5">
                                        <button type="button" class="bulk-tog bulk-tog-on" id="bulk-tog-db"
                                            onclick="bulkSetFiling('db')">
                                            Database only
                                        </button>
                                        <button type="button" class="bulk-tog" id="bulk-tog-job"
                                            onclick="bulkSetFiling('job')">
                                            + Assign job
                                        </button>
                                    </div>
                                </div>

                                {{-- Job selector (visible when "assign job" is active) --}}
                                <div id="bulk-job-selector" class="flex-1 min-w-0" style="display:none;">
                                    <label class="text-xs text-gray-400 mb-1 block">@lang('menu.jobs')</label>
                                    <select id="bulk-job-select" class="bulk-input text-xs w-full"
                                        onchange="bulkGetQuestions(this.value)">
                                        <option value="">— choose a job —</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}"
                                                data-job-id="{{ $location->job_id }}"
                                                data-loc-id="{{ $location->location_id }}">
                                                {{ ucwords($location->job->title) }} ({{ ucwords($location->location->location) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Navigation & save --}}
                                <div class="flex items-center gap-2 ml-auto flex-shrink-0">
                                    <button type="button" class="bulk-nav-btn" onclick="bulkStep(-1)">
                                        <i class="fa fa-arrow-left"></i> Prev
                                    </button>
                                    <span class="text-xs text-gray-400 whitespace-nowrap" id="bulk-counter">0 of 0</span>
                                    <button type="button" class="bulk-nav-btn" onclick="bulkStep(1)">
                                        Next <i class="fa fa-arrow-right"></i>
                                    </button>
                                    <button type="button" id="bulk-save-btn" onclick="bulkSaveCurrent()"
                                        class="bulk-save-btn">
                                        <i class="fa fa-check mr-1"></i> Save &amp; next
                                    </button>
                                </div>
                            </div>
                        </div>{{-- /bulk-review-pane --}}
                    </div>{{-- /bulk-col-right --}}
                </div>{{-- /bulk-layout --}}

            </div>
        </div>
    </div>
</div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mammoth@1.8.0/mammoth.browser.min.js"></script>

    <style>
        /* ── Layout ── */
        .bulk-layout {
            display: grid;
            grid-template-columns: 230px 1fr;
            gap: 16px;
            min-height: 600px;
        }
        @media (max-width: 768px) {
            .bulk-layout { grid-template-columns: 1fr; }
        }

        /* ── Left column ── */
        .bulk-col-left {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .bulk-dropzone {
            border: 1.5px dashed #d1d5db;
            border-radius: 10px;
            padding: 16px 12px;
            text-align: center;
            cursor: pointer;
            background: #f9fafb;
            transition: border-color .15s, background .15s;
            flex-shrink: 0;
        }
        .bulk-dropzone:hover,
        .bulk-dropzone.over {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .bulk-queue-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-height: 200px;
            max-height: 420px;
            padding-right: 2px;
        }
        .bulk-q-item {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 7px 9px;
            border-radius: 8px;
            cursor: pointer;
            border: 0.5px solid transparent;
            transition: background .12s;
        }
        .bulk-q-item:hover  { background: #f3f4f6; }
        .bulk-q-item.active { background: #eff6ff; border-color: #bfdbfe; }
        .bulk-q-item.active .bulk-q-name { color: #1d4ed8; font-weight: 500; }
        .bulk-q-dot {
            width: 20px; height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            flex-shrink: 0;
        }
        .bulk-dot-pending { background: #f3f4f6; color: #9ca3af; border: 0.5px solid #e5e7eb; }
        .bulk-dot-parsing  { background: #fef3c7; color: #92400e; }
        .bulk-dot-done     { background: #d1fae5; color: #065f46; }
        .bulk-dot-saved    { background: #dbeafe; color: #1e40af; }
        .bulk-dot-error    { background: #fee2e2; color: #991b1b; }
        .bulk-q-name {
            flex: 1;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #374151;
        }
        .bulk-q-del {
            font-size: 11px;
            color: #d1d5db;
            opacity: 0;
            cursor: pointer;
            padding: 2px 3px;
            border-radius: 4px;
            border: none;
            background: transparent;
        }
        .bulk-q-item:hover .bulk-q-del { opacity: 1; }
        .bulk-q-del:hover { color: #dc2626; background: #fee2e2; }

        /* ── Right column ── */
        .bulk-col-right {
            display: flex;
            flex-direction: column;
        }
        .bulk-empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: center;
            border: 1.5px dashed #e5e7eb;
            border-radius: 12px;
            padding: 40px;
        }
        .bulk-review-pane {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0;
            border: 0.5px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .bulk-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            flex: 1;
            min-height: 0;
        }
        @media (max-width: 900px) {
            .bulk-split { grid-template-columns: 1fr; }
        }

        /* ── CV Viewer ── */
        .bulk-cv-viewer {
            height: 400px;
            overflow-y: auto;
            padding: 14px;
            background: #f9fafb;
            border-right: 0.5px solid #e5e7eb;
        }
        .bulk-cv-page {
            background: #fff;
            border: 0.5px solid #e5e7eb;
            border-radius: 6px;
            padding: 18px 20px;
            min-height: 100%;
        }
        .bulk-cv-page * { color: #1f2937 !important; }
        .bulk-cv-name    { font-size: 16px; font-weight: 700; margin: 0 0 2px; }
        .bulk-cv-contact { font-size: 10.5px; color: #6b7280 !important; margin: 0 0 10px; }
        .bulk-cv-sec {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 2px;
            margin: 10px 0 5px;
        }
        .bulk-cv-li {
            font-size: 10.5px;
            margin: 0 0 3px;
            padding-left: 10px;
            position: relative;
            line-height: 1.5;
        }
        .bulk-cv-li:before { content: "•"; position: absolute; left: 0; }
        .bulk-cv-skills    { font-size: 10.5px; line-height: 1.6; }
        .bulk-hl           { background: #fef9c3; padding: 0 2px; border-radius: 2px; }
        .bulk-raw-text {
            font-size: 11px;
            line-height: 1.7;
            white-space: pre-wrap;
            word-break: break-word;
            color: #374151;
        }

        /* ── Form scroll area ── */
        .bulk-form-scroll {
            height: 400px;
            overflow-y: auto;
            padding: 14px 16px;
        }
        .bulk-fg           { margin-bottom: 10px; }
        .bulk-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11.5px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 3px;
        }
        .bulk-input {
            width: 100%;
            font-size: 12.5px;
            padding: 5px 8px;
            border: 0.5px solid #d1d5db;
            border-radius: 8px;
            background: #fff;
            color: #111827;
            outline: none;
            box-sizing: border-box;
        }
        .bulk-input:focus   { border-color: #2563eb; }
        .bulk-input.bulk-parsed { border-color: #059669; background: #f0fdf4; }
        .bulk-conf {
            font-size: 10px;
            padding: 1px 6px;
            border-radius: 8px;
            font-weight: 500;
        }
        .bulk-conf-hi  { background: #d1fae5; color: #065f46; }
        .bulk-conf-lo  { background: #fef3c7; color: #92400e; }

        /* ── Note pills ── */
        .bulk-note-pill {
            display: flex;
            align-items: start;
            justify-content: space-between;
            background: #f9fafb;
            border-left: 3px solid #60a5fa;
            border-radius: 6px;
            padding: 6px 8px;
            font-size: 11.5px;
            gap: 6px;
        }

        /* ── Filing bar ── */
        .bulk-filing-bar {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 14px;
            border-top: 0.5px solid #e5e7eb;
            background: #f9fafb;
            flex-wrap: wrap;
        }
        .bulk-tog {
            font-size: 11.5px;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 8px;
            border: 0.5px solid #d1d5db;
            cursor: pointer;
            background: transparent;
            color: #6b7280;
            transition: background .12s, border-color .12s, color .12s;
            white-space: nowrap;
        }
        .bulk-tog.bulk-tog-on {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }
        .bulk-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 8px;
            border: 0.5px solid #d1d5db;
            background: transparent;
            color: #6b7280;
            cursor: pointer;
        }
        .bulk-nav-btn:hover { background: #f3f4f6; }
        .bulk-save-btn {
            font-size: 12.5px;
            font-weight: 500;
            padding: 7px 16px;
            border-radius: 8px;
            border: none;
            background: #059669;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
        }
        .bulk-save-btn:hover  { background: #047857; }
        .bulk-save-btn:disabled { opacity: .5; cursor: not-allowed; }

        /* spinner */
        @keyframes bulk-spin { to { transform: rotate(360deg); } }
        .bulk-spin { display: inline-block; animation: bulk-spin .7s linear infinite; }
    </style>

    <script>
        /* ─────────────────────────────────────────────
           Laravel route & config variables
        ───────────────────────────────────────────── */
        const bulkCsrfToken          = "{{ csrf_token() }}";
        const bulkStoreUrl           = "{{ route('admin.job-applications.store') }}";
        const bulkIndexUrl           = "{{ route('admin.job-applications.index') }}";
        const bulkQuestionRouteBase  = "{{ route('admin.job-applications.question', ':id') }}";
        const bulkFetchCountryState  = "{{ route('jobs.fetchCountryState') }}";
        const bulkSelectCountry      = "@lang('modules.front.selectCountry')";
        const bulkSelectState        = "@lang('modules.front.selectState')";
        const bulkSelectCity         = "@lang('modules.front.selectCity')";

        /* ─────────────────────────────────────────────
           State
        ───────────────────────────────────────────── */
        var bulkQueue    = [];   // array of item objects
        var bulkActive   = -1;
        var bulkFiling   = 'db';
        var bulkNotes    = [];

        /* ─────────────────────────────────────────────
           File input / drop zone
        ───────────────────────────────────────────── */
        document.getElementById('bulk-file-input').addEventListener('change', function () {
            bulkAddFiles(this.files);
            this.value = '';
        });

        function bulkOnDragOver(e) {
            e.preventDefault();
            document.getElementById('bulk-dropzone').classList.add('over');
        }
        function bulkOnDragLeave(e) {
            document.getElementById('bulk-dropzone').classList.remove('over');
        }
        function bulkOnDrop(e) {
            e.preventDefault();
            document.getElementById('bulk-dropzone').classList.remove('over');
            if (e.dataTransfer && e.dataTransfer.files) bulkAddFiles(e.dataTransfer.files);
        }

        function bulkAddFiles(files) {
            if (!files || !files.length) return;
            Array.from(files).forEach(function (f) {
                var alreadyAdded = bulkQueue.some(function (q) {
                    return q.file && q.file.name === f.name;
                });
                if (!alreadyAdded) {
                    bulkQueue.push({
                        file:    f,
                        name:    f.name.replace(/\.[^.]+$/, ''),
                        status:  'pending',   // pending | parsing | done | error
                        parsed:  null,
                        saved:   false,
                        notes:   [],
                        filing:  'db',
                        jobLocId: '',
                        jobId:   '',
                        locId:   '',
                    });
                }
            });
            bulkRenderQueue();
            bulkUpdateBatchBar();
            if (bulkActive === -1 && bulkQueue.length > 0) bulkSelectItem(0);
        }

        /* ─────────────────────────────────────────────
           Queue render
        ───────────────────────────────────────────── */
        function bulkRenderQueue() {
            var list  = document.getElementById('bulk-q-list');
            var empty = document.getElementById('bulk-q-empty');

            document.getElementById('bulk-q-count').textContent =
                bulkQueue.length + ' file' + (bulkQueue.length !== 1 ? 's' : '');
            document.getElementById('bulk-progress-pill').style.display = bulkQueue.length ? '' : 'none';
            document.getElementById('bulk-total-count').textContent = bulkQueue.length;

            if (!bulkQueue.length) {
                empty.style.display = '';
                list.style.display  = 'none';
                list.innerHTML      = '';
                bulkUpdateProgress();
                return;
            }

            empty.style.display = 'none';
            list.style.display  = 'flex';

            list.innerHTML = bulkQueue.map(function (item, i) {
                var dotCls = 'bulk-dot-pending', iconCls = 'fa-clock-o';
                if (item.status === 'parsing')  { dotCls = 'bulk-dot-parsing'; iconCls = 'fa-spinner bulk-spin'; }
                else if (item.saved)            { dotCls = 'bulk-dot-saved';   iconCls = 'fa-check'; }
                else if (item.status === 'done'){ dotCls = 'bulk-dot-done';    iconCls = 'fa-check'; }
                else if (item.status === 'error'){ dotCls = 'bulk-dot-error';  iconCls = 'fa-exclamation-triangle'; }

                return '<div class="bulk-q-item' + (i === bulkActive ? ' active' : '') + '" onclick="bulkSelectItem(' + i + ')">' +
                    '<span class="bulk-q-dot ' + dotCls + '"><i class="fa ' + iconCls + '"></i></span>' +
                    '<span class="bulk-q-name" title="' + bulkEsc(item.name) + '">' + bulkEsc(item.name) + '</span>' +
                    '<button class="bulk-q-del" type="button" onclick="event.stopPropagation();bulkRemoveItem(' + i + ')" title="Remove"><i class="fa fa-times"></i></button>' +
                '</div>';
            }).join('');

            bulkUpdateProgress();
        }

        function bulkRemoveItem(i) {
            bulkQueue.splice(i, 1);
            if (bulkActive >= bulkQueue.length) bulkActive = bulkQueue.length - 1;
            if (bulkActive === i) bulkActive = -1;
            bulkRenderQueue();
            bulkUpdateBatchBar();
            if (bulkActive >= 0) bulkSelectItem(bulkActive);
            else bulkShowEmptyState();
        }

        /* ─────────────────────────────────────────────
           Select item → parse if needed
        ───────────────────────────────────────────── */
        function bulkSelectItem(i) {
            // Save current form edits back to queue before switching
            if (bulkActive >= 0 && bulkActive !== i) bulkSnapshotForm(bulkActive);

            bulkActive = i;
            bulkRenderQueue();
            var item = bulkQueue[i];
            if (!item) return;

            document.getElementById('bulk-empty-state').style.display  = 'none';
            document.getElementById('bulk-review-pane').style.display  = 'flex';
            bulkUpdateCounter();

            // Restore filing mode for this item
            bulkSetFiling(item.filing || 'db', false);

            if (item.status === 'done' || item.saved) {
                bulkRenderCV(item);
                bulkFillForm(item);
            } else if (item.status === 'parsing') {
                bulkShowParsingState(item.name);
            } else {
                bulkParseItem(i);
            }
        }

        /* ─────────────────────────────────────────────
           Parse a single CV using existing helpers
        ───────────────────────────────────────────── */
        function bulkParseItem(i) {
            var item = bulkQueue[i];
            item.status = 'parsing';
            bulkRenderQueue();
            bulkShowParsingState(item.name);

            bulkExtractResumeText(item.file)
                .then(function (text) {
                    if (!String(text || '').trim()) throw 'No readable text found in this CV.';

                    var data      = bulkParseResumeText(text);
                    item.parsed   = data;
                    item.status   = 'done';

                    // Store raw text for AI cover-letter generation
                    item.resumeText = text;

                    bulkRenderQueue();
                    bulkUpdateBatchBar();

                    if (bulkActive === i) {
                        bulkRenderCV(item);
                        bulkFillForm(item);
                    }
                })
                .catch(function (err) {
                    item.status = 'error';
                    item.parsed = null;
                    bulkRenderQueue();
                    if (bulkActive === i) {
                        document.getElementById('bulk-cv-viewer').innerHTML =
                            '<div class="flex flex-col items-center justify-center h-full gap-2 text-red-400">' +
                            '<i class="fa fa-exclamation-triangle fa-2x"></i>' +
                            '<p class="text-xs text-center">' + bulkEsc(String(err)) + '</p></div>';
                    }
                });
        }

        /* ─────────────────────────────────────────────
           CV renderer (shows raw extracted text with
           name/email/phone/skills highlighted)
        ───────────────────────────────────────────── */
        function bulkRenderCV(item) {
            var d   = item.parsed || {};
            var raw = item.resumeText || '';

            // Build a minimal structured preview if we have parsed fields,
            // otherwise fall back to raw text
            var html = '<div class="bulk-cv-page">';

            if (d.full_name) {
                html += '<p class="bulk-cv-name"><span class="bulk-hl-name">' + bulkEsc(d.full_name) + '</span></p>';
                var contact = [d.email, d.phone, d.address].filter(Boolean);
                if (contact.length) {
                    html += '<p class="bulk-cv-contact">';
                    if (d.email)   html += '<span class="bulk-hl-email">' + bulkEsc(d.email) + '</span>';
                    if (d.phone)   html += (d.email ? ' · ' : '') + '<span class="bulk-hl-phone">' + bulkEsc(d.phone) + '</span>';
                    if (d.address) html += (d.email || d.phone ? ' · ' : '') + '<span class="bulk-hl-addr">' + bulkEsc(d.address) + '</span>';
                    html += '</p>';
                }
            }

            if (d.skills) {
                html += '<div class="bulk-cv-sec">Skills</div>';
                html += '<p class="bulk-cv-skills"><span class="bulk-hl-skills">' + bulkEsc(d.skills) + '</span></p>';
            }

            if (raw) {
                // Show the raw text (truncated) below parsed fields
                html += '<div class="bulk-cv-sec">Full text</div>';
                html += '<pre class="bulk-raw-text">' + bulkEsc(raw.substring(0, 3000)) + (raw.length > 3000 ? '\n…' : '') + '</pre>';
            }

            html += '</div>';
            document.getElementById('bulk-cv-viewer').innerHTML = html;
        }

        function bulkShowParsingState(name) {
            document.getElementById('bulk-cv-viewer').innerHTML =
                '<div class="flex flex-col items-center justify-center h-full gap-2 text-gray-400">' +
                '<i class="fa fa-spinner fa-spin fa-2x"></i>' +
                '<p class="text-xs">Parsing ' + bulkEsc(name) + '...</p></div>';
        }

        /* ─────────────────────────────────────────────
           Fill form from parsed data
        ───────────────────────────────────────────── */
        function bulkFillForm(item) {
            var d = item.parsed || {};

            // Header
            var initial = (d.full_name || '?').charAt(0).toUpperCase();
            document.getElementById('bulk-rev-avatar').textContent   = initial;
            document.getElementById('bulk-rev-name').textContent     = d.full_name || item.name;
            document.getElementById('bulk-rev-file').textContent     = item.file ? item.file.name : '';

            var flag = document.getElementById('bulk-rev-flag');
            if (item.saved) {
                flag.className   = 'text-xs px-2 py-0.5 rounded-full font-medium bg-blue-100 text-blue-700';
                flag.textContent = '✓ saved';
            } else if (item.status === 'done') {
                flag.className   = 'text-xs px-2 py-0.5 rounded-full font-medium bg-green-100 text-green-700';
                flag.textContent = 'parsed';
            } else {
                flag.className   = 'text-xs px-2 py-0.5 rounded-full font-medium bg-yellow-100 text-yellow-700';
                flag.textContent = 'needs review';
            }

            // Fields
            bulkSetInput('bf-name',    d.full_name,  !!d.full_name);
            bulkSetInput('bf-email',   d.email,      !!d.email);
            bulkSetInput('bf-phone',   d.phone,      !!d.phone);
            bulkSetInput('bf-skills',  d.skills,     !!d.skills);
            bulkSetTextarea('bf-address', d.address, !!d.address);

            // Confidence badges
            bulkSetConf('bconf-email',  d.email);
            bulkSetConf('bconf-phone',  d.phone);

            // Resume text for AI
            document.getElementById('bulk-resume-text-for-ai').value = item.resumeText || '';

            // Notes
            bulkNotes = item.notes ? item.notes.slice() : [];
            bulkRenderNotes();

            // Job/location hidden fields
            document.getElementById('bulk-job-id').value  = item.jobId  || '';
            document.getElementById('bulk-location-id').value = item.locId || '';
            if (item.jobLocId) {
                document.getElementById('bulk-job-select').value = item.jobLocId;
            } else {
                document.getElementById('bulk-job-select').value = '';
                document.getElementById('bulk-question-section').style.display = 'none';
                document.getElementById('bulk-question-box').innerHTML = '';
                document.getElementById('bulk-show-columns').innerHTML = '';
                document.getElementById('bulk-show-sections').innerHTML = '';
            }
        }

        function bulkSetInput(id, val, parsed) {
            var el = document.getElementById(id);
            if (!el) return;
            el.value = val || '';
            el.classList.toggle('bulk-parsed', !!parsed);
        }
        function bulkSetTextarea(id, val, parsed) {
            var el = document.getElementById(id);
            if (!el) return;
            el.value = val || '';
            el.classList.toggle('bulk-parsed', !!parsed);
        }
        function bulkSetConf(id, val) {
            var el = document.getElementById(id);
            if (!el) return;
            if (val) {
                el.className   = 'bulk-conf bulk-conf-hi';
                el.textContent = 'high';
            } else {
                el.className   = 'bulk-conf bulk-conf-lo';
                el.textContent = 'check';
            }
        }

        /* Snapshot form values back into the queue item so switching away doesn't lose edits */
        function bulkSnapshotForm(i) {
            var item = bulkQueue[i];
            if (!item) return;
            // Always snapshot notes and filing regardless of parse status
            item.notes   = bulkNotes.slice();
            item.filing  = bulkFiling;
            var jobSelEl = document.getElementById('bulk-job-select');
            if (jobSelEl) item.jobLocId = jobSelEl.value;
            var jobIdEl = document.getElementById('bulk-job-id');
            if (jobIdEl) item.jobId = jobIdEl.value;
            var locIdEl = document.getElementById('bulk-location-id');
            if (locIdEl) item.locId = locIdEl.value;
            // Only snapshot parsed text fields if we have a parsed object
            if (item.parsed) {
                var nameEl    = document.getElementById('bf-name');
                var emailEl   = document.getElementById('bf-email');
                var phoneEl   = document.getElementById('bf-phone');
                var skillsEl  = document.getElementById('bf-skills');
                var addressEl = document.getElementById('bf-address');
                if (nameEl)    item.parsed.full_name = nameEl.value;
                if (emailEl)   item.parsed.email     = emailEl.value;
                if (phoneEl)   item.parsed.phone     = phoneEl.value;
                if (skillsEl)  item.parsed.skills    = skillsEl.value;
                if (addressEl) item.parsed.address   = addressEl.value;
            }
        }

        /* ─────────────────────────────────────────────
           Highlight CV text on form field hover
        ───────────────────────────────────────────── */
        var bulkHlMap = {
            name:   '.bulk-hl-name',
            email:  '.bulk-hl-email',
            phone:  '.bulk-hl-phone',
            skills: '.bulk-hl-skills',
        };
        function bulkHl(key) {
            document.querySelectorAll('#bulk-cv-viewer .bulk-hl').forEach(function (el) {
                el.classList.remove('bulk-hl');
            });
            var sel = bulkHlMap[key];
            if (sel) {
                var el = document.querySelector('#bulk-cv-viewer ' + sel);
                if (el) { el.classList.add('bulk-hl'); el.scrollIntoView({ block: 'center', behavior: 'smooth' }); }
            }
        }

        /* ─────────────────────────────────────────────
           Filing mode toggle
        ───────────────────────────────────────────── */
        function bulkSetFiling(mode, persist) {
            bulkFiling = mode;
            if (persist !== false && bulkActive >= 0) bulkQueue[bulkActive].filing = mode;
            document.getElementById('bulk-tog-db').classList.toggle('bulk-tog-on', mode === 'db');
            document.getElementById('bulk-tog-job').classList.toggle('bulk-tog-on', mode === 'job');
            document.getElementById('bulk-job-selector').style.display = mode === 'job' ? '' : 'none';
        }

        /* ─────────────────────────────────────────────
           Job questions (same logic as original create)
        ───────────────────────────────────────────── */
        function bulkGetQuestions(jobLocId) {
            // Store job/location ids
            var sel  = document.getElementById('bulk-job-select');
            var opt  = sel.options[sel.selectedIndex];
            var jobId = opt ? (opt.getAttribute('data-job-id') || '') : '';
            var locId = opt ? (opt.getAttribute('data-loc-id') || '') : '';

            document.getElementById('bulk-job-id').value      = jobId;
            document.getElementById('bulk-location-id').value = locId;

            if (bulkActive >= 0) {
                bulkQueue[bulkActive].jobLocId = jobLocId;
                bulkQueue[bulkActive].jobId    = jobId;
                bulkQueue[bulkActive].locId    = locId;
            }

            if (!jobLocId) {
                document.getElementById('bulk-question-section').style.display = 'none';
                document.getElementById('bulk-question-box').innerHTML = '';
                document.getElementById('bulk-show-columns').innerHTML = '';
                document.getElementById('bulk-show-sections').innerHTML = '';
                return;
            }

            var url = bulkQuestionRouteBase.replace(':id', jobLocId);
            $.easyAjax({
                type: 'GET',
                url:  url,
                container: '#bulk-candidate-form',
                success: function (response) {
                    document.getElementById('bulk-job-id').value      = response.jobJobLocation.job_id;
                    document.getElementById('bulk-location-id').value = response.jobJobLocation.location_id;

                    if (bulkActive >= 0) {
                        bulkQueue[bulkActive].jobId = response.jobJobLocation.job_id;
                        bulkQueue[bulkActive].locId = response.jobJobLocation.location_id;
                    }

                    if (response.count > 0) {
                        document.getElementById('bulk-question-section').style.display = '';
                        document.getElementById('bulk-question-box').innerHTML = response.view;
                    } else {
                        document.getElementById('bulk-question-section').style.display = 'none';
                        document.getElementById('bulk-question-box').innerHTML = '';
                    }
                    document.getElementById('bulk-show-columns').innerHTML  = response.requiredColumnsView || '';
                    document.getElementById('bulk-show-sections').innerHTML = response.requiredSectionsView || '';

                    if (response.requiredColumnsView) {
                        $('.dob').datepicker({ autoclose: true, format: 'yyyy-mm-dd', endDate: (new Date()).toDateString() });
                        $('.select2').select2({ width: '100%' });
                        var loc = new locationInfo();
                        loc.getCountries();
                    }
                }
            });
        }

        /* ─────────────────────────────────────────────
           Notes
        ───────────────────────────────────────────── */
        document.getElementById('bulk-add-note').addEventListener('click', function () {
            var text = document.getElementById('bulk-notes-input').value.trim();
            if (!text) return;
            bulkNotes.push(text);
            document.getElementById('bulk-notes-input').value = '';
            bulkRenderNotes();
        });

        function bulkRenderNotes() {
            var list   = document.getElementById('bulk-notes-list');
            var hidden = document.getElementById('bulk-notes-hidden');
            list.innerHTML = bulkNotes.map(function (n, i) {
                if (n === null) return '';
                return '<div class="bulk-note-pill" id="bni-' + i + '">' +
                    '<span class="text-gray-700 flex-1 text-xs">' + bulkEsc(n) + '</span>' +
                    '<button type="button" class="text-red-400 hover:text-red-600 text-xs" onclick="bulkRemoveNote(' + i + ')"><i class="fa fa-times"></i></button>' +
                '</div>';
            }).join('');
            hidden.innerHTML = bulkNotes.filter(Boolean).map(function (n) {
                return '<input type="hidden" name="notes[]" value="' + bulkEsc(n) + '">';
            }).join('');
        }

        function bulkRemoveNote(i) {
            bulkNotes[i] = null;
            bulkRenderNotes();
        }

        /* ─────────────────────────────────────────────
           Save current candidate
        ───────────────────────────────────────────── */
        function bulkSaveCurrent() {
            if (bulkActive < 0) return;
            bulkSnapshotForm(bulkActive);
            var item = bulkQueue[bulkActive];
            if (!item) return;

            var saveBtn = document.getElementById('bulk-save-btn');
            var prevHtml = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Saving…';

            var fd = new FormData();
            fd.append('_token',     bulkCsrfToken);
            fd.append('full_name',  document.getElementById('bf-name').value);
            fd.append('email',      document.getElementById('bf-email').value);
            fd.append('phone',      document.getElementById('bf-phone').value);
            fd.append('skills',     document.getElementById('bf-skills').value);
            fd.append('address',    document.getElementById('bf-address').value);

            // Filing
            if (bulkFiling === 'db') {
                fd.append('entry_type', 'candidate');
            } else {
                fd.append('entry_type',  'applicant');
                fd.append('job_id',      document.getElementById('bulk-job-id').value);
                fd.append('location_id', document.getElementById('bulk-location-id').value);
            }

            // Notes
            bulkNotes.filter(Boolean).forEach(function (n) { fd.append('notes[]', n); });

            // Resume file
            if (item.file) fd.append('resume', item.file);

            // Answers (from question boxes)
            var form = document.getElementById('bulk-candidate-form');
            var answerFields = form.querySelectorAll('[name^="answer"]');
            answerFields.forEach(function (el) { fd.append(el.name, el.value); });

            // Required columns
            var colFields = form.querySelectorAll('#bulk-show-columns input, #bulk-show-columns select, #bulk-show-sections input, #bulk-show-sections select');
            colFields.forEach(function (el) { if (el.name) fd.append(el.name, el.value); });

            $.ajax({
                url:         bulkStoreUrl,
                type:        'POST',
                data:        fd,
                processData: false,
                contentType: false,
                dataType:    'json',
                success: function (response) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = prevHtml;
                    if (response && response.status === 'success') {
                        item.saved  = true;
                        item.status = 'done';
                        bulkRenderQueue();
                        bulkUpdateProgress();
                        bulkUpdateBatchBar();

                        var next = bulkFindNext();
                        if (next !== -1) {
                            bulkSelectItem(next);
                        } else {
                            bulkShowAllDoneState();
                        }
                    }
                },
                error: function (response) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = prevHtml;
                    bulkHandleFails(response);
                }
            });
        }

        /* ─────────────────────────────────────────────
           Batch approve all parsed → database
        ───────────────────────────────────────────── */
        document.getElementById('bulk-approve-all').addEventListener('click', function () {
            var toApprove = bulkQueue.filter(function (q) { return q.status === 'done' && !q.saved; });
            if (!toApprove.length) return;

            var btn = this;
            btn.disabled  = true;
            btn.textContent = 'Saving…';

            var index = 0;
            function saveNext() {
                if (index >= toApprove.length) {
                    btn.disabled = false;
                    btn.textContent = 'Done ✓';
                    bulkRenderQueue();
                    bulkUpdateProgress();
                    bulkUpdateBatchBar();
                    bulkShowAllDoneState();
                    return;
                }
                var item = toApprove[index];
                index++;

                var fd = new FormData();
                fd.append('_token',     bulkCsrfToken);
                fd.append('entry_type', 'candidate');
                fd.append('full_name',  (item.parsed && item.parsed.full_name) || item.name);
                fd.append('email',      (item.parsed && item.parsed.email)     || '');
                fd.append('phone',      (item.parsed && item.parsed.phone)     || '');
                fd.append('skills',     (item.parsed && item.parsed.skills)    || '');
                fd.append('address',    (item.parsed && item.parsed.address)   || '');
                if (item.file) fd.append('resume', item.file);

                $.ajax({
                    url: bulkStoreUrl, type: 'POST',
                    data: fd, processData: false, contentType: false, dataType: 'json',
                    success: function (r) {
                        if (r && r.status === 'success') { item.saved = true; item.status = 'done'; }
                        saveNext();
                    },
                    error: function () { saveNext(); }
                });
            }
            saveNext();
        });

        /* ─────────────────────────────────────────────
           Navigation (Prev / Next)
        ───────────────────────────────────────────── */
        function bulkStep(dir) {
            if (bulkActive >= 0) bulkSnapshotForm(bulkActive);
            var next = Math.max(0, Math.min(bulkQueue.length - 1, bulkActive + dir));
            if (next !== bulkActive) bulkSelectItem(next);
        }

        function bulkFindNext() {
            for (var i = bulkActive + 1; i < bulkQueue.length; i++) {
                if (!bulkQueue[i].saved) return i;
            }
            for (var j = 0; j < bulkActive; j++) {
                if (!bulkQueue[j].saved) return j;
            }
            return -1;
        }

        /* ─────────────────────────────────────────────
           UI helpers
        ───────────────────────────────────────────── */
        function bulkUpdateProgress() {
            var done  = bulkQueue.filter(function (q) { return q.saved; }).length;
            var total = bulkQueue.length;
            var pct   = total ? Math.round(done / total * 100) : 0;
            document.getElementById('bulk-done-count').textContent  = done;
            document.getElementById('bulk-prog-label').textContent  = done + ' of ' + total + ' reviewed';
            document.getElementById('bulk-prog-pct').textContent    = pct + '%';
            document.getElementById('bulk-prog-fill').style.width   = pct + '%';
        }

        function bulkUpdateBatchBar() {
            var done    = bulkQueue.filter(function (q) { return q.status === 'done' && !q.saved; }).length;
            var bar     = document.getElementById('bulk-batch-bar');
            if (done > 1) {
                bar.style.display = 'flex';
                document.getElementById('bulk-batch-msg').textContent =
                    done + ' CVs parsed — approve all directly to the candidate database, or review individually.';
            } else {
                bar.style.display = 'none';
            }
        }

        function bulkUpdateCounter() {
            document.getElementById('bulk-counter').textContent =
                (bulkActive + 1) + ' of ' + bulkQueue.length;
        }

        function bulkShowEmptyState() {
            document.getElementById('bulk-empty-state').style.display  = '';
            document.getElementById('bulk-review-pane').style.display  = 'none';
        }

        function bulkShowAllDoneState() {
            document.getElementById('bulk-cv-viewer').innerHTML =
                '<div class="flex flex-col items-center justify-center h-full gap-2 text-green-600">' +
                '<i class="fa fa-check-circle fa-3x"></i>' +
                '<p class="text-sm font-medium">All CVs reviewed!</p>' +
                '<a href="' + bulkIndexUrl + '" class="text-xs text-blue-600 underline mt-1">Go to applicants board →</a>' +
                '</div>';
        }

        /* ─────────────────────────────────────────────
           Error handler (mirrors original handleFails)
        ───────────────────────────────────────────── */
        function bulkHandleFails(response) {
            if (!response.responseJSON || !response.responseJSON.errors) return;
            var errors = response.responseJSON.errors;
            var form   = document.getElementById('bulk-candidate-form');
            Object.keys(errors).forEach(function (key) {
                var el  = form.querySelector('[name="' + key + '"]');
                var grp = el ? el.closest('.bulk-fg') : null;
                if (grp) {
                    grp.querySelector('.bulk-err') && grp.querySelector('.bulk-err').remove();
                    var msg = document.createElement('p');
                    msg.className   = 'bulk-err text-red-500 text-xs mt-1';
                    msg.textContent = errors[key][0] || errors[key];
                    grp.appendChild(msg);
                }
            });
        }

        /* ─────────────────────────────────────────────
           ── CV text extraction (pdf.js / mammoth / txt)
        ───────────────────────────────────────────── */
        function bulkExtractResumeText(file) {
            var name = (file.name || '').toLowerCase();
            var type = (file.type || '').toLowerCase();
            if (type.indexOf('pdf') >= 0 || name.endsWith('.pdf'))    return bulkExtractPdf(file);
            if (name.endsWith('.docx'))                               return bulkExtractDocx(file);
            if (type.indexOf('text') >= 0 || name.endsWith('.txt'))   return bulkReadText(file);
            return Promise.reject('Please upload a PDF, DOCX, or TXT file.');
        }

        function bulkReadArrayBuffer(file) {
            return new Promise(function (resolve, reject) {
                var r = new FileReader();
                r.onload  = function (e) { resolve(e.target.result); };
                r.onerror = function ()  { reject('Unable to read file.'); };
                r.readAsArrayBuffer(file);
            });
        }
        function bulkReadText(file) {
            return new Promise(function (resolve, reject) {
                var r = new FileReader();
                r.onload  = function (e) { resolve(e.target.result || ''); };
                r.onerror = function ()  { reject('Unable to read file.'); };
                r.readAsText(file);
            });
        }
        function bulkExtractPdf(file) {
            if (typeof pdfjsLib === 'undefined') return Promise.reject('PDF parser not loaded.');
            if (pdfjsLib.GlobalWorkerOptions) {
                pdfjsLib.GlobalWorkerOptions.workerSrc =
                    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            }
            return bulkReadArrayBuffer(file).then(function (buf) {
                return pdfjsLib.getDocument({ data: buf }).promise;
            }).then(function (pdf) {
                var pages = [];
                for (var i = 1; i <= pdf.numPages; i++) {
                    pages.push(pdf.getPage(i).then(function (p) {
                        return p.getTextContent();
                    }).then(function (c) {
                        return c.items.map(function (it) { return it.str; }).join(' ');
                    }));
                }
                return Promise.all(pages).then(function (ps) { return ps.join('\n'); });
            });
        }
        function bulkExtractDocx(file) {
            if (typeof mammoth === 'undefined') return Promise.reject('DOCX parser not loaded.');
            return bulkReadArrayBuffer(file).then(function (buf) {
                return mammoth.extractRawText({ arrayBuffer: buf });
            }).then(function (r) { return r.value || ''; });
        }

        /* ─────────────────────────────────────────────
           ── CV parsing helpers (mirrors original)
        ───────────────────────────────────────────── */
        function bulkParseResumeText(text) {
            var clean  = String(text || '').replace(/\r/g, '\n').replace(/\t/g, ' ');
            var emailM = clean.match(/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i);
            var phoneM = clean.match(/(?:\+?\d[\d\s().\-]{7,}\d)/);
            return {
                full_name: bulkPickName(clean),
                email:     emailM ? emailM[0] : '',
                phone:     phoneM ? phoneM[0].replace(/\s+/g, ' ').trim() : '',
                skills:    bulkParseSkills(clean),
                address:   bulkParseAddress(clean),
                resume_text: clean
            };
        }

        function bulkPickName(text) {
            var lines = text.split(/\n+/).map(function (l) { return l.replace(/\s+/g, ' ').trim(); }).filter(Boolean);
            var bad = /^(resume|curriculum vitae|cv|profile|summary|objective|contact|email|phone|mobile|address|skills|education|experience|work experience|certifications|references|declaration|languages|hobbies|interests|achievements)$/i;
            for (var i = 0; i < Math.min(lines.length, 15); i++) {
                var l = lines[i];
                if (bad.test(l.trim()) || l.indexOf('@') >= 0 || /\d/.test(l)) continue;
                if (l.length > 60 || l.length < 3) continue;
                if (/^[A-Za-z][A-Za-z .'\\-]{2,59}$/.test(l) && l.split(/\s+/).length >= 2 && l.split(/\s+/).length <= 5) return l;
            }
            return '';
        }

        function bulkParseAddress(text) {
            var m = text.match(/(?:address|location|residence|residing at)[:\s]+([^\n]{5,120}(?:\n[^\n]{0,80}){0,2})/i);
            if (m && m[1]) return m[1].replace(/\n/g, ', ').replace(/,\s*,/g, ',').trim();
            var lines = text.split(/\n/);
            for (var i = 0; i < lines.length; i++) {
                var l = lines[i].trim();
                if (/^\d+[\s,]+[A-Za-z]/.test(l) && l.length > 8 && l.length < 120) {
                    var addr = l;
                    if (lines[i+1] && lines[i+1].trim().length > 2 && lines[i+1].trim().length < 80) addr += ', ' + lines[i+1].trim();
                    return addr;
                }
            }
            return '';
        }

        function bulkParseSkills(text) {
            var known = ['PHP','Laravel','JavaScript','TypeScript','Vue','React','Angular','Node.js','Express',
                'HTML','CSS','Tailwind','Bootstrap','jQuery','MySQL','PostgreSQL','MongoDB','Redis',
                'Git','Docker','Kubernetes','AWS','Azure','GCP','Python','Django','Flask','Java',
                'Spring','C#','.NET','SQL','REST API','GraphQL','Excel','Power BI','Tableau',
                'Communication','Leadership','Project Management','Sales','Marketing','Recruitment'];
            var found = [];
            known.forEach(function (sk) {
                var pat = new RegExp('(^|[^a-zA-Z0-9+#.])' + sk.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '([^a-zA-Z0-9+#.]|$)', 'i');
                if (pat.test(text)) found.push(sk);
            });
            var sec = text.match(/(?:^|\n)\s*(?:technical skills|key skills|skills)\s*[:\n]+([\s\S]{0,900}?)(?=\n\s*(?:experience|work experience|employment|education|projects|certifications|summary|profile|objective|languages)\b|$)/i);
            if (sec && sec[1]) {
                sec[1].split(/[,|;\n\-]+/).forEach(function (s) {
                    var sk = s.replace(/\s+/g, ' ').trim();
                    if (sk && sk.length <= 40) found.push(sk);
                });
            }
            var unique = [];
            found.forEach(function (s) {
                if (unique.map(function (u) { return u.toLowerCase(); }).indexOf(s.toLowerCase()) === -1) unique.push(s);
            });
            return unique.slice(0, 30).join(', ');
        }

        /* ─────────────────────────────────────────────
           Utility
        ───────────────────────────────────────────── */
        function bulkEsc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    </script>

    {{-- Location script (used for required-columns country/state/city) --}}
    <script>
        const fetchCountryState = bulkFetchCountryState;
        const csrfToken  = bulkCsrfToken;
        const selectCountry = bulkSelectCountry;
        const selectState   = bulkSelectState;
        const selectCity    = bulkSelectCity;
        let country = '', state = '';
    </script>
    <script src="{{ asset('front/assets/js/location.js') }}"></script>
@endpush