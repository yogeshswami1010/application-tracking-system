@extends('layouts.app')

@push('head-script')
<style>
    .ai-search-page {
        font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        min-height: calc(100dvh - 9.5rem);
        background: #EEF0F5;
        margin: -1rem -1.5rem;
        padding: 2rem 1.5rem;
    }

    /* ── Header ── */
    .ai-search-hero {
        text-align: center;
        margin-bottom: 2rem;
    }
    .ai-search-hero-icon {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        box-shadow: 0 8px 24px rgba(37,99,235,.25);
    }
    .ai-search-hero h1 {
        font-size: 22px;
        font-weight: 700;
        color: #1a1e2e;
        margin: 0 0 6px;
    }
    .ai-search-hero p {
        font-size: 13.5px;
        color: #8892a0;
        margin: 0;
    }

    /* ── Search box ── */
    .ai-search-box-wrap {
        max-width: 680px;
        margin: 0 auto 2rem;
    }
    .ai-search-box {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        border: 1.5px solid #e2ded8;
        border-radius: 14px;
        padding: 10px 14px;
        transition: border-color .15s, box-shadow .15s;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .ai-search-box:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37,99,235,.08), 0 2px 12px rgba(0,0,0,.06);
    }
    .ai-search-box-icon {
        color: #2563eb;
        flex-shrink: 0;
    }
    .ai-search-input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 14.5px;
        color: #1a1e2e;
        background: transparent;
        font-family: inherit;
    }
    .ai-search-input::placeholder { color: #b0b8c4; }
    .ai-search-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13.5px;
        font-weight: 600;
        color: #fff;
        background: #2563eb;
        border: none;
        border-radius: 10px;
        padding: 8px 20px;
        cursor: pointer;
        font-family: inherit;
        transition: background .12s;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .ai-search-btn:hover { background: #1d4ed8; }
    .ai-search-btn:disabled { opacity: .6; cursor: not-allowed; }

    /* ── Suggestions ── */
    .ai-search-suggestions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
        margin-top: 14px;
    }
    .ai-search-suggestion {
        font-size: 12px;
        font-weight: 500;
        padding: 5px 14px;
        border-radius: 20px;
        border: 1px solid #e2ded8;
        background: #fff;
        color: #5a6478;
        cursor: pointer;
        transition: all .12s;
    }
    .ai-search-suggestion:hover {
        border-color: #2563eb;
        color: #2563eb;
        background: #eff6ff;
    }

    /* ── Status bar ── */
    .ai-search-status-bar {
        max-width: 880px;
        margin: 0 auto 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .ai-search-status-label {
        font-size: 13px;
        font-weight: 600;
        color: #5a6478;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ai-search-status-label span {
        color: #1a1e2e;
    }
    .ai-search-clear-btn {
        font-size: 12.5px;
        color: #8892a0;
        background: #fff;
        border: 1px solid #e2ded8;
        border-radius: 8px;
        padding: 5px 12px;
        cursor: pointer;
        font-family: inherit;
        transition: all .12s;
    }
    .ai-search-clear-btn:hover { border-color: #dc2626; color: #dc2626; }

    /* ── Sort bar ── */
    .ai-search-sort-bar {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ai-search-sort-label {
        font-size: 12px;
        color: #8892a0;
    }
    .ai-search-sort-btn {
        font-size: 12px;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #e2ded8;
        background: #fff;
        color: #5a6478;
        cursor: pointer;
        font-family: inherit;
        transition: all .12s;
    }
    .ai-search-sort-btn.active {
        background: #1a1e2e;
        color: #fff;
        border-color: #1a1e2e;
    }

    /* ── Results grid ── */
    .ai-search-results-grid {
        max-width: 880px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    /* ── Result card ── */
    .ai-result-card {
        background: #fff;
        border: 0.5px solid #e8e6e1;
        border-radius: 14px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        cursor: pointer;
        transition: border-color .12s, box-shadow .12s, transform .1s;
        position: relative;
    }
    .ai-result-card:hover {
        border-color: #2563eb;
        box-shadow: 0 4px 20px rgba(37,99,235,.1);
        transform: translateY(-1px);
    }
    .ai-result-rank {
        font-size: 11px;
        font-weight: 700;
        color: #b0b8c4;
        width: 20px;
        text-align: center;
        flex-shrink: 0;
    }
    .ai-result-rank.top { color: #f59e0b; }
    .ai-result-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #dbeafe;
        color: #1d4ed8;
        font-weight: 700;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .ai-result-body { flex: 1; min-width: 0; }
    .ai-result-name {
        font-size: 14.5px;
        font-weight: 700;
        color: #1a1e2e;
        margin: 0 0 2px;
    }
    .ai-result-meta {
        font-size: 12px;
        color: #8892a0;
        margin: 0 0 6px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }
    .ai-result-meta-sep {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: #d1d5db;
        display: inline-block;
    }
    .ai-result-status {
        display: inline-flex;
        align-items: center;
        padding: 1px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
    }
    .ai-result-skills {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 4px;
    }
    .ai-result-skill-tag {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 20px;
        background: #f1f3f7;
        color: #5a6478;
        font-weight: 500;
    }
    .ai-result-skill-tag.matched {
        background: #d1fae5;
        color: #065f46;
    }
    .ai-result-score-col {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 5px;
        flex-shrink: 0;
        min-width: 70px;
    }
    .ai-result-score-pct {
        font-size: 18px;
        font-weight: 800;
        line-height: 1;
    }
    .ai-result-score-label {
        font-size: 10px;
        color: #8892a0;
        font-weight: 500;
    }
    .ai-result-score-bar-wrap {
        width: 60px;
        height: 5px;
        background: #e5e7eb;
        border-radius: 5px;
        overflow: hidden;
    }
    .ai-result-score-bar {
        height: 100%;
        border-radius: 5px;
        transition: width .5s ease;
    }
    .ai-result-view-btn {
        font-size: 12px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 8px;
        border: 1.5px solid #e2ded8;
        background: #fff;
        color: #5a6478;
        cursor: pointer;
        font-family: inherit;
        flex-shrink: 0;
        transition: all .12s;
    }
    .ai-result-view-btn:hover {
        border-color: #2563eb;
        color: #2563eb;
        background: #eff6ff;
    }

    /* ── Empty / loading states ── */
    .ai-search-empty {
        max-width: 880px;
        margin: 0 auto;
        text-align: center;
        padding: 60px 20px;
        color: #8892a0;
    }
    .ai-search-empty-icon {
        font-size: 40px;
        opacity: .2;
        margin-bottom: 14px;
    }
    .ai-search-empty h3 {
        font-size: 16px;
        font-weight: 600;
        color: #5a6478;
        margin: 0 0 6px;
    }
    .ai-search-empty p {
        font-size: 13px;
        margin: 0;
    }
    .ai-search-loading {
        max-width: 880px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .ai-search-skeleton {
        background: #fff;
        border: 0.5px solid #e8e6e1;
        border-radius: 14px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .ai-skel-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #f1f3f7;
        flex-shrink: 0;
        animation: ai-skel-pulse 1.4s ease-in-out infinite;
    }
    .ai-skel-body { flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .ai-skel-line {
        height: 12px;
        border-radius: 6px;
        background: #f1f3f7;
        animation: ai-skel-pulse 1.4s ease-in-out infinite;
    }
    .ai-skel-line.short { width: 40%; }
    .ai-skel-line.mid   { width: 65%; }
    @keyframes ai-skel-pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: .5; }
    }

    /* ── AI thinking badge ── */
    .ai-thinking-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #7c3aed;
        background: #f5f3ff;
        border: 1px solid #ddd6fe;
        border-radius: 20px;
        padding: 4px 12px;
    }
</style>
@endpush

@section('content')
<div class="ai-search-page">

    {{-- Hero --}}
    <div class="ai-search-hero">
        <div class="ai-search-hero-icon">
            <svg width="26" height="26" fill="none" stroke="#fff" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
        </div>
        <h1>AI Candidate Search</h1>
        <p>Search across all applicant skills, CVs and cover letters using natural language</p>
    </div>

    {{-- Search box --}}
    <div class="ai-search-box-wrap">
        <div class="ai-search-box">
            <div class="ai-search-box-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" id="ai-search-input" class="ai-search-input"
                placeholder="Try: web developer, Python backend, sales manager with 5 years..."
                onkeydown="if(event.key==='Enter') aiDoSearch()">
            <button type="button" class="ai-search-btn" id="ai-search-btn" onclick="aiDoSearch()">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Search with AI
            </button>
        </div>

        {{-- Suggestions --}}
        <div class="ai-search-suggestions" id="ai-suggestions">
            <span class="ai-search-suggestion" onclick="aiQuick('Web Developer in Toronto')">Web Developer in Toronto</span>
            <span class="ai-search-suggestion" onclick="aiQuick('Welder with 5 years experience')">Welder 5 yrs exp</span>
            <span class="ai-search-suggestion" onclick="aiQuick('Laravel PHP Developer')">Laravel PHP</span>
            <span class="ai-search-suggestion" onclick="aiQuick('Sales Manager')">Sales Manager</span>
            <span class="ai-search-suggestion" onclick="aiQuick('React Frontend Developer')">React Frontend</span>
            <span class="ai-search-suggestion" onclick="aiQuick('Data Analyst in Vancouver')">Data Analyst Vancouver</span>
            <span class="ai-search-suggestion" onclick="aiQuick('Python Backend 3 years experience')">Python 3yr exp</span>
            <span class="ai-search-suggestion" onclick="aiQuick('Graphic Designer')">Graphic Designer</span>
        </div>
    </div>

    {{-- Index CVs admin panel --}}
    <div style="max-width:680px;margin:0 auto 20px;display:flex;align-items:center;justify-content:flex-end;gap:10px;" id="ai-index-bar">
        <span style="font-size:11.5px;color:#b0b8c4" id="ai-index-status"></span>
        <button type="button" onclick="aiIndexCvs()" id="ai-index-btn"
            style="font-size:12px;font-weight:600;padding:6px 14px;border-radius:9px;border:1px solid #e2ded8;background:#fff;color:#5a6478;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;">
            <i class="fa fa-database"></i> Index CVs for search
        </button>
    </div>

    {{-- Output area --}}
    <div id="ai-search-output">
        {{-- Default empty state --}}
        <div class="ai-search-empty" id="ai-empty-state">
            <div class="ai-search-empty-icon"><i class="fa fa-search"></i></div>
            <h3>Search your candidate database</h3>
            <p>Search by role, location, experience — AI reads uploaded CVs to find the best matches</p>
        </div>
    </div>

</div>

{{-- Applicant sidebar (reuse existing) --}}
@endsection

@push('footer-script')
<script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}"></script>
<script>
    // ── Sidebar close handlers ──────────────────────────────
        // ── Watch for sidebar closing and clear backdrop ────────
    var sidebarObserver = new MutationObserver(function () {
        var sidebar = document.getElementById('right-sidebar');
        if (!sidebar) return;
        if (sidebar.classList.contains('translate-x-full')) {
            var backdrop = document.getElementById('right-sidebar-backdrop');
            if (backdrop) {
                backdrop.classList.add('hidden');
                backdrop.style.display    = 'none';
                backdrop.style.visibility = 'hidden';
            }
        }
    });

    var sidebar = document.getElementById('right-sidebar');
    if (sidebar) {
        sidebarObserver.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
    }

    // Also force-clear backdrop on any click outside sidebar content
    document.addEventListener('click', function (e) {
        var sidebar  = document.getElementById('right-sidebar');
        var backdrop = document.getElementById('right-sidebar-backdrop');
        if (!sidebar || !backdrop) return;

        var isInsideSidebar  = sidebar.contains(e.target);
        var isResultCard     = e.target.closest('.ai-result-card') || e.target.closest('.ai-result-view-btn');

        if (!isInsideSidebar && !isResultCard) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('translate-x-full');
            backdrop.classList.add('hidden');
            backdrop.style.display    = 'none';
            backdrop.style.visibility = 'hidden';
        }
    });
    var aiCurrentSort = 'score';
    var aiLastResults = [];
    var aiLastQuery   = '';
    var aiLastMeta    = { location: '', min_experience: 0 };

    // ── Quick suggestion click ──────────────────────────────────
    function aiQuick(text) {
        document.getElementById('ai-search-input').value = text;
        aiDoSearch();
    }

    // ── Main search ─────────────────────────────────────────────
    function aiDoSearch() {
        var query = document.getElementById('ai-search-input').value.trim();
        if (!query) return;

        aiLastQuery = query;
        document.getElementById('ai-suggestions').style.display = 'none';
        aiShowLoading();

        var btn = document.getElementById('ai-search-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Thinking…';

        // Step 1: Ollama (local AI) parses the query server-side — no API key in browser
        $.ajax({
            url: '{{ route("admin.ai-search.parse-query") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', query: query },
            success: function(res) {
                var parsed = res.data || res;

                var allTerms = []
                    .concat(parsed.skills   || [])
                    .concat(parsed.keywords || [])
                    .concat(parsed.roles    || []);

                aiLastMeta = {
                    location:       parsed.location       || '',
                    min_experience: parseInt(parsed.min_experience) || 0
                };

                // Step 2: Search server with full context
                aiSearchServer(query, allTerms, parsed.location || '', parseInt(parsed.min_experience) || 0);
            },
            error: function() {
                // Fallback: treat raw query as keyword
                aiSearchServer(query, [query], '', 0);
            }
        });
    }

    // ── Server search ────────────────────────────────────────────
    function aiSearchServer(query, terms, location, minExp) {
        $.ajax({
            url: '{{ route("admin.ai-search.results") }}',
            type: 'GET',
            data: { query: query, terms: terms, location: location, min_experience: minExp },
            success: function(res) {
                var btn = document.getElementById('ai-search-btn');
                btn.disabled = false;
                btn.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg> Search with AI';
                aiLastResults = res.results || [];
                aiRenderResults(aiLastResults, query);
            },
            error: function() {
                var btn = document.getElementById('ai-search-btn');
                btn.disabled = false;
                btn.innerHTML = 'Search with AI';
                aiRenderResults([], query);
            }
        });
    }

    // ── Bulk CV text indexing ────────────────────────────────────
    function aiIndexCvs() {
        var btn    = document.getElementById('ai-index-btn');
        var status = document.getElementById('ai-index-status');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Indexing…';
        status.textContent = '';

        function runBatch() {
            $.ajax({
                url: '{{ route("admin.ai-search.index-cvs") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', limit: 20 },
                success: function(res) {
                    var d = res.data || res;
                    status.textContent = d.processed + ' indexed, ' + d.failed + ' skipped, ' + d.remaining + ' remaining';
                    if (d.remaining > 0) {
                        setTimeout(runBatch, 800);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-check"></i> All CVs indexed';
                        setTimeout(function() {
                            btn.innerHTML = '<i class="fa fa-database"></i> Index CVs for search';
                        }, 4000);
                    }
                },
                error: function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-database"></i> Index CVs for search';
                    status.textContent = 'Indexing failed — check server log.';
                }
            });
        }
        runBatch();
    }

    // ── Render results ───────────────────────────────────────────
    function aiRenderResults(results, query) {
        var out = document.getElementById('ai-search-output');

        if (!results.length) {
            out.innerHTML =
                '<div class="ai-search-empty">' +
                    '<div class="ai-search-empty-icon"><i class="fa fa-user-times"></i></div>' +
                    '<h3>No matches found</h3>' +
                    '<p>No candidates matched "<strong>' + aiEsc(query) + '</strong>" — try different keywords</p>' +
                    '<button onclick="aiClear()" style="margin-top:14px;font-size:13px;font-weight:600;padding:8px 20px;border-radius:10px;border:1.5px solid #e2ded8;background:#fff;color:#5a6478;cursor:pointer;font-family:inherit;">Clear search</button>' +
                '</div>';
            return;
        }

        var filterBadges = '';
        if (aiLastMeta.location) {
            filterBadges += '<span style="font-size:11px;font-weight:600;color:#2563eb;background:#eff6ff;padding:3px 9px;border-radius:12px;"><i class="fa fa-map-marker" style="font-size:10px"></i> ' + aiEsc(aiLastMeta.location) + '</span>';
        }
        if (aiLastMeta.min_experience > 0) {
            filterBadges += '<span style="font-size:11px;font-weight:600;color:#7c3aed;background:#f5f3ff;padding:3px 9px;border-radius:12px;"><i class="fa fa-clock-o" style="font-size:10px"></i> ' + aiLastMeta.min_experience + '+ yrs exp</span>';
        }

        var html =
            '<div class="ai-search-status-bar">' +
                '<div class="ai-search-status-label" style="flex-wrap:wrap;">' +
                    '<span class="ai-thinking-badge"><i class="fa fa-magic"></i> AI matched</span>' +
                    '<span>' + results.length + ' candidate' + (results.length !== 1 ? 's' : '') + '</span> for "' + aiEsc(query) + '"' +
                    (filterBadges ? '<span style="display:inline-flex;gap:5px;margin-left:4px;">' + filterBadges + '</span>' : '') +
                '</div>' +
                '<div style="display:flex;align-items:center;gap:10px;">' +
                    '<div class="ai-search-sort-bar">' +
                        '<span class="ai-search-sort-label">Sort:</span>' +
                        '<button class="ai-search-sort-btn ' + (aiCurrentSort==='score'?'active':'') + '" onclick="aiSort(\'score\')">Best match</button>' +
                        '<button class="ai-search-sort-btn ' + (aiCurrentSort==='name'?'active':'') + '" onclick="aiSort(\'name\')">Name</button>' +
                        '<button class="ai-search-sort-btn ' + (aiCurrentSort==='recent'?'active':'') + '" onclick="aiSort(\'recent\')">Recent</button>' +
                    '</div>' +
                    '<button class="ai-search-clear-btn" onclick="aiClear()"><i class="fa fa-times"></i> Clear</button>' +
                '</div>' +
            '</div>' +
            '<div class="ai-search-results-grid" id="ai-results-grid"></div>';

        out.innerHTML = html;
        aiRenderCards(results);
    }

    function aiRenderCards(results) {
        var grid = document.getElementById('ai-results-grid');
        if (!grid) return;

        var html = '';
        results.forEach(function(r, idx) {
            var initial    = (r.full_name || '?').charAt(0).toUpperCase();
            var scoreColor = r.score >= 75 ? '#059669' : r.score >= 45 ? '#2563eb' : '#8892a0';
            var isTop      = idx < 3;

            // Avatar color based on initial
            var avatarColors = ['#dbeafe:#1d4ed8','#d1fae5:#065f46','#fef3c7:#92400e','#fce7f3:#9d174d','#ede9fe:#5b21b6'];
            var ac = avatarColors[r.full_name.charCodeAt(0) % avatarColors.length].split(':');

            var skillsHtml = '';
            if (r.all_skills && r.all_skills.length) {
                skillsHtml = '<div class="ai-result-skills">' +
                    r.all_skills.slice(0, 8).map(function(s) {
                        var isMatch = r.matched_skills && r.matched_skills.indexOf(s) > -1;
                        return '<span class="ai-result-skill-tag' + (isMatch ? ' matched' : '') + '">' + aiEsc(s) + '</span>';
                    }).join('') +
                    (r.all_skills.length > 8 ? '<span class="ai-result-skill-tag">+' + (r.all_skills.length - 8) + '</span>' : '') +
                '</div>';
            }

            var cvBadge = r.has_cv
                ? '<span style="font-size:10px;font-weight:600;color:#059669;background:#d1fae5;padding:2px 7px;border-radius:10px;margin-left:6px;"><i class="fa fa-file-text-o" style="font-size:9px"></i> CV indexed</span>'
                : '<span style="font-size:10px;color:#b0b8c4;background:#f1f3f7;padding:2px 7px;border-radius:10px;margin-left:6px;">No CV</span>';

            html +=
                '<div class="ai-result-card" onclick="aiOpenApplicant(' + r.id + ')">' +
                    '<div class="ai-result-rank ' + (isTop ? 'top' : '') + '">' + (isTop ? '★' : (idx+1)) + '</div>' +
                    '<div class="ai-result-avatar" style="background:' + ac[0] + ';color:' + ac[1] + '">' + aiEsc(initial) + '</div>' +
                    '<div class="ai-result-body">' +
                        '<div class="ai-result-name">' + aiEsc(r.full_name) + cvBadge + '</div>' +
                        '<div class="ai-result-meta">' +
                            aiEsc(r.job_title || '—') +
                            '<span class="ai-result-meta-sep"></span>' +
                            '<i class="fa fa-map-marker" style="font-size:10px;color:#8892a0;margin-right:2px"></i>' + aiEsc(r.location || '—') +
                            '<span class="ai-result-meta-sep"></span>' +
                            '<span class="ai-result-status" style="background:' + aiEsc(r.status_color || '#6b7280') + '">' + aiEsc(r.status || '—') + '</span>' +
                        '</div>' +
                        skillsHtml +
                    '</div>' +
                    '<div class="ai-result-score-col">' +
                        '<div class="ai-result-score-pct" style="color:' + scoreColor + '">' + r.score + '%</div>' +
                        '<div class="ai-result-score-label">match</div>' +
                        '<div class="ai-result-score-bar-wrap">' +
                            '<div class="ai-result-score-bar" style="width:' + r.score + '%;background:' + scoreColor + '"></div>' +
                        '</div>' +
                    '</div>' +
                    '<button class="ai-result-view-btn" onclick="event.stopPropagation();aiOpenApplicant(' + r.id + ')">View <i class="fa fa-arrow-right" style="font-size:10px"></i></button>' +
                '</div>';
        });

        grid.innerHTML = html;
    }

    // ── Sort ─────────────────────────────────────────────────────
    function aiSort(by) {
        aiCurrentSort = by;
        var sorted = aiLastResults.slice();
        if (by === 'score') {
            sorted.sort(function(a,b){ return b.score - a.score; });
        } else if (by === 'name') {
            sorted.sort(function(a,b){ return (a.full_name||'').localeCompare(b.full_name||''); });
        } else if (by === 'recent') {
            sorted.sort(function(a,b){ return b.id - a.id; });
        }
        // Re-render sort buttons
        document.querySelectorAll('.ai-search-sort-btn').forEach(function(btn) {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
        aiRenderCards(sorted);
    }

    // ── Clear ────────────────────────────────────────────────────
    function aiClear() {
        document.getElementById('ai-search-input').value = '';
        document.getElementById('ai-suggestions').style.display = 'flex';
        aiLastResults = [];
        aiLastQuery   = '';
        document.getElementById('ai-search-output').innerHTML =
            '<div class="ai-search-empty" id="ai-empty-state">' +
                '<div class="ai-search-empty-icon"><i class="fa fa-search"></i></div>' +
                '<h3>Search your candidate database</h3>' +
                '<p>Type a role, skill, or description — AI will find the best matches from all applicants</p>' +
            '</div>';
    }

    // ── Loading skeletons ────────────────────────────────────────
    function aiShowLoading() {
        var html = '<div class="ai-search-loading">';
        for (var i = 0; i < 5; i++) {
            html +=
                '<div class="ai-search-skeleton">' +
                    '<div class="ai-skel-circle"></div>' +
                    '<div class="ai-skel-body">' +
                        '<div class="ai-skel-line mid"></div>' +
                        '<div class="ai-skel-line short"></div>' +
                    '</div>' +
                '</div>';
        }
        html += '</div>';
        document.getElementById('ai-search-output').innerHTML = html;
    }

    // ── Open applicant sidebar ───────────────────────────────────
    function aiOpenApplicant(id) {
        var url = "{{ route('admin.job-applications.show', ':id') }}".replace(':id', id);
        var $sidebar  = $('#right-sidebar');
        var $backdrop = $('#right-sidebar-backdrop');
        $sidebar.removeClass('translate-x-full').addClass('translate-x-0');
        $backdrop.removeClass('hidden').css({ display:'block', visibility:'visible' });
        $.easyAjax({
            type: 'GET', url: url,
            success: function(response) {
                if (response.status === 'success') {
                    $('#right-sidebar-content').html(response.view);
                }
            }
        });
    }

    function aiEsc(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
</script>
@endpush