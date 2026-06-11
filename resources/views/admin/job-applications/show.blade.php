<link rel="stylesheet" href="{{ asset('assets/plugins/jquery-bar-rating-master/dist/themes/fontawesome-stars.css') }}">
@php
    $detailCatName = $application->job?->category?->name ?? __('app.category');
    $detailCatKey = \Illuminate\Support\Str::slug($detailCatName);
    $detailCatClass = match (true) {
        str_contains($detailCatKey, 'engineer') || str_contains($detailCatKey, 'tech') || str_contains($detailCatKey, 'it') => 'bg-[#EFF6FF] text-[#1D4ED8]',
        str_contains($detailCatKey, 'sale') || str_contains($detailCatKey, 'market') => 'bg-[#FFF7ED] text-[#C2410C]',
        str_contains($detailCatKey, 'content') || str_contains($detailCatKey, 'design') => 'bg-[#ECFDF5] text-[#065F46]',
        str_contains($detailCatKey, 'hr') || str_contains($detailCatKey, 'people') => 'bg-[#F5F3FF] text-[#5B21B6]',
        default => 'bg-[#F1F3F7] text-[#5A6478]',
    };
    $stagePillBg = $application->status->color ?? '#0F1F3D';
    $initials = collect(explode(' ', $application->full_name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
    $allStatuses = \App\ApplicationStatus::orderBy('position')->get();
    $currentStatusId = $application->status_id;
    $currentStatus = $allStatuses->firstWhere('id', $currentStatusId);

    // ── Previous applications (same email) ──
    $previousApps = \App\JobApplication::where('email', $application->email)
        ->where('is_candidate', 0)
        ->where('id', '!=', $application->id)
        ->with(['job:id,title', 'status:id,status,color'])
        ->orderByDesc('created_at')
        ->get();
   
    $clientNotes = \App\JobClientNote::with('user:id,name')
        ->where('job_id', $application->job_id)
        ->orderByDesc('created_at')
        ->get();
    // Resolve resume URL
    $resumeUrl = null;

    // Application resume
    if (!empty($application->resume_url)) {
        $resumeUrl = $application->resume_url;
    }

    // Resume uploaded through custom questions
    if (!$resumeUrl && !empty($answers)) {
        foreach ($answers as $answer) {
            if (!empty($answer->file)) {
                if (!empty($answer->file_url)) {
                    $resumeUrl = $answer->file_url;
                } else {
                    $resumeUrl = url('user-uploads/documents/' . basename($answer->file));
                }
                break;
            }
        }
    }
@endphp

{{-- 
  ============================================================
  TWO-COLUMN APPLICANT DETAIL PANEL
  Left:  PDF resume viewer (pdf.js iframe or embed)
  Right: Tabbed detail panel (Details / Notes / Q&A / Schedule)
  Width: Controlled by the parent drawer (set to 75vw in JS)
  ============================================================
--}}

<style>
.ja-two-col-wrap {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background: #F8F7F4;
    font-family: 'Plus Jakarta Sans', sans-serif;
    overflow: hidden;
}

/* ── Header ── */
.ja-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    background: #0F1F3D;
    flex-shrink: 0;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.ja-header-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,.25);
    object-fit: cover; flex-shrink: 0;
}
.ja-header-avatar-initials {
    width: 38px; height: 38px; border-radius: 50%;
    background: rgba(255,255,255,.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.ja-header-meta { flex: 1; min-width: 0; }
.ja-header-meta h2 {
    font-size: 15px; font-weight: 700; color: #fff;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0;
}
.ja-header-meta p {
    font-size: 11.5px; color: rgba(255,255,255,.5); margin: 2px 0 0;
}
.ja-header-pills { display: flex; align-items: center; gap: 6px; }
.ja-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.ja-pill-stage { color: #fff; }
.ja-pill-cat { background: rgba(255,255,255,.1); color: rgba(255,255,255,.75); }
.ja-close-btn {
    width: 30px; height: 30px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,.15); background: rgba(255,255,255,.07);
    color: rgba(255,255,255,.7); cursor: pointer; display: flex;
    align-items: center; justify-content: center; flex-shrink: 0;
    transition: background .15s;
}
.ja-close-btn:hover { background: rgba(255,255,255,.15); }

/* ── Prev / Next navigation ── */
.ja-nav-arrows {
    display: flex; align-items: center; gap: 4px;
    background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
    border-radius: 10px; padding: 3px 6px;
    flex-shrink: 0;
}
.ja-nav-btn {
    width: 26px; height: 26px; border-radius: 7px;
    border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.75); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s, color .15s, opacity .15s;
    flex-shrink: 0;
}
.ja-nav-btn:hover:not(:disabled) { background: rgba(255,255,255,.18); color: #fff; }
.ja-nav-btn:disabled { opacity: .25; cursor: not-allowed; }
.ja-nav-counter {
    font-size: 11px; font-weight: 600; color: rgba(255,255,255,.5);
    min-width: 36px; text-align: center; white-space: nowrap;
}

/* ── Body: two columns ── */
.ja-body {
    flex: 1; display: grid;
    grid-template-columns: 1fr 380px;
    overflow: hidden; min-height: 0;
}

/* ── LEFT: PDF viewer ── */
.ja-pdf-panel {
    display: flex; flex-direction: column;
    border-right: 1px solid #E8E6E1; overflow: hidden; background: #525659;
}
.ja-pdf-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 9px 14px; background: #fff; border-bottom: 1px solid #E8E6E1;
    flex-shrink: 0;
}
.ja-pdf-toolbar-label {
    display: flex; align-items: center; gap: 7px;
    font-size: 12px; font-weight: 600; color: #5A6478;
}
.ja-pdf-toolbar-actions { display: flex; align-items: center; gap: 6px; }
.ja-pdf-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 11px; border-radius: 8px;
    border: 1px solid #E2DED8; background: #fff; cursor: pointer;
    font-size: 12px; color: #5A6478; font-family: 'Plus Jakarta Sans', sans-serif;
    transition: background .15s, color .15s;
}
.ja-pdf-btn:hover { background: #F8F7F4; color: #1A1E2E; }
.ja-pdf-btn-primary {
    background: #2563EB; color: #fff; border-color: transparent;
}
.ja-pdf-btn-primary:hover { background: #1d4ed8; color: #fff; }
.ja-pdf-no-resume {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center; color: #aaa; text-align: center;
    padding: 40px;
}
.ja-pdf-no-resume i { font-size: 48px; opacity: .35; display: block; margin-bottom: 14px; }
.ja-pdf-no-resume p { font-size: 13px; opacity: .6; }
.ja-pdf-frame {
    flex: 1; border: none; width: 100%; height: 100%;
    display: block;
}

/* ── RIGHT: Tab panel ── */
.ja-right-panel {
    display: flex; flex-direction: column;
    overflow: hidden; background: #F8F7F4;
}
.ja-tabs {
    display: flex; background: #fff; border-bottom: 1px solid #E8E6E1;
    flex-shrink: 0; padding: 0 16px; overflow-x: auto;
}
.ja-tab {
    padding: 11px 13px; font-size: 12.5px; font-weight: 600; color: #8A94A6;
    cursor: pointer; border-bottom: 2.5px solid transparent; white-space: nowrap;
    display: flex; align-items: center; gap: 5px; transition: color .15s;
    flex-shrink: 0;
}
.ja-tab.active { color: #2563EB; border-bottom-color: #2563EB; }
.ja-tab:hover:not(.active) { color: #1A1E2E; }
.ja-tab-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 18px; height: 18px; padding: 0 5px; border-radius: 20px;
    background: #F0EEE9; font-size: 10px; font-weight: 600; color: #8A94A6;
}
.ja-right-scroll {
    flex: 1; overflow-y: auto; padding: 12px;
}

/* ── Section cards ── */
.ja-card {
    background: #fff; border: 1px solid #E8E6E1; border-radius: 14px;
    padding: 14px 16px; margin-bottom: 10px;
    box-shadow: 0 1px 3px rgba(15,31,61,.04);
}
.ja-card-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; color: #B0B8C4; margin-bottom: 12px;
    display: flex; align-items: center; gap: 6px;
}
.ja-info-row {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 7px 0; border-bottom: 1px solid #F0EEE9;
}
.ja-info-row:last-child { border-bottom: none; padding-bottom: 0; }
.ja-info-label {
    font-size: 11.5px; color: #8A94A6; display: flex; align-items: center;
    gap: 5px; flex-shrink: 0; width: 105px;
}
.ja-info-val {
    font-size: 12.5px; font-weight: 600; color: #1A1E2E;
    text-align: right; word-break: break-all;
}
.ja-info-val a { color: #2563EB; text-decoration: none; }
.ja-info-val a:hover { text-decoration: underline; }

/* ── Stage mover ── */
.ja-stage-row { display: flex; align-items: center; gap: 8px; }
.ja-current-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 11px; border-radius: 20px;
    font-size: 11.5px; font-weight: 600; color: #fff; flex-shrink: 0;
}
.ja-stage-select {
    flex: 1; padding: 7px 10px; border-radius: 9px;
    border: 1.5px solid #E2DED8; background: #fff;
    font-size: 12.5px; font-weight: 500; color: #5A6478;
    cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif;
    transition: border-color .15s;
}
.ja-stage-select:hover, .ja-stage-select:focus { border-color: #2563EB; outline: none; }

/* ── Stars ── */
.ja-stars { display: flex; align-items: center; gap: 3px; }
.ja-star {
    font-size: 20px; cursor: pointer; color: #D1D5DB; transition: color .1s;
}
.ja-star.on { color: #F59E0B; }

/* ── Notes ── */
.ja-note {
    background: #fff; border-radius: 0 10px 10px 0;
    border: 1px solid #E8E6E1; border-left: 3px solid #2563EB;
    padding: 10px 13px; margin-bottom: 8px;
}
.ja-note-meta {
    display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px;
}
.ja-note-author {
    font-size: 11.5px; font-weight: 600; color: #1A1E2E;
    display: flex; align-items: center; gap: 5px;
}
.ja-note-time { font-size: 10.5px; color: #B0B8C4; }
.ja-note-body { font-size: 12.5px; color: #5A6478; line-height: 1.6; }
.ja-note-actions { display: flex; gap: 5px; margin-top: 7px; }
.ja-note-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 7px; border: 1px solid #E8E6E1;
    background: transparent; cursor: pointer; font-size: 11px; color: #8A94A6;
    font-family: 'Plus Jakarta Sans', sans-serif; transition: background .12s;
}
.ja-note-btn:hover { background: #F8F7F4; color: #1A1E2E; }
.ja-add-note {
    background: #fff; border: 1px solid #E8E6E1; border-radius: 14px; padding: 14px;
}
.ja-note-textarea {
    width: 100%; border: 1px solid #E2DED8; border-radius: 9px;
    padding: 9px 12px; font-size: 12.5px; font-family: 'Plus Jakarta Sans', sans-serif;
    color: #1A1E2E; background: #F8F7F4; resize: none; outline: none;
    transition: border-color .15s; line-height: 1.6;
}
.ja-note-textarea:focus { border-color: #2563EB; background: #fff; }
.ja-save-note-btn {
    margin-top: 8px; display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 9px; border: none;
    background: #2563EB; color: #fff; cursor: pointer;
    font-size: 12.5px; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif;
    transition: background .15s;
}
.ja-save-note-btn:hover { background: #1d4ed8; }

/* ── Q&A ── */
.ja-qa-item {
    background: #fff; border: 1px solid #E8E6E1; border-radius: 10px;
    padding: 11px 13px; margin-bottom: 8px;
    border-left: 3px solid #E8E6E1;
}
.ja-qa-q { font-size: 12px; font-weight: 600; color: #1A1E2E; margin-bottom: 7px; }
.ja-qa-badge {
    display: inline-flex; align-items: center; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
}
.ja-qa-yes { background: #ECFDF5; color: #065F46; }
.ja-qa-no  { background: #FEF2F2; color: #991B1B; }

/* ── Skills ── */
.ja-skills-wrap { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 12px; }
.ja-skill-tag {
    display: inline-flex; align-items: center; padding: 5px 12px;
    border-radius: 20px; font-size: 12px; font-weight: 500;
    background: #F1F3F7; color: #5A6478; border: 1px solid #E2DED8;
}

/* ── Action buttons ── */
.ja-action-btns { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
.ja-btn {
    flex: 1; min-width: 110px; display: inline-flex; align-items: center;
    justify-content: center; gap: 6px; padding: 8px 12px; border-radius: 10px;
    border: none; cursor: pointer; font-size: 12.5px; font-weight: 600;
    font-family: 'Plus Jakarta Sans', sans-serif; transition: opacity .15s;
}
.ja-btn:hover { opacity: .85; }
.ja-btn-blue  { background: #EFF6FF; color: #1D4ED8; }
.ja-btn-green { background: #ECFDF5; color: #065F46; }
.ja-btn-red   { background: #FFF1F2; color: #EF4444; }

/* ── Interview card ── */
.ja-schedule-card {
    background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 10px; padding: 12px 14px;
}
.ja-schedule-date { font-size: 14px; font-weight: 700; color: #065F46; margin-bottom: 3px; }
.ja-schedule-type { font-size: 12px; color: #059669; }
.ja-interviewer-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 10px; background: #F8F7F4; border-radius: 9px;
    border: 1px solid #E8E6E1; margin-bottom: 6px;
}
.ja-interviewer-avatar {
    width: 28px; height: 28px; border-radius: 50%; background: #EFF6FF;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; color: #1D4ED8; flex-shrink: 0;
}
.ja-badge-accept { background: #ECFDF5; color: #065F46; }
.ja-badge-refuse { background: #FEF2F2; color: #991B1B; }
.ja-badge-pending { background: #FFF7ED; color: #92400E; }
.ja-small-badge {
    font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 20px;
}

/* ── Nav loading shimmer ── */
.ja-nav-loading {
    opacity: .5;
    pointer-events: none;
    transition: opacity .2s;
}
</style>

<div class="ja-two-col-wrap">

    {{-- ── HEADER ── --}}
    <div class="ja-header">
        <img src="{{ $application->photo_url }}" alt=""
             class="ja-header-avatar"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
        <div class="ja-header-avatar-initials" style="display:none">{{ $initials }}</div>

        <div class="ja-header-meta">
            <h2>{{ ucwords($application->full_name) }}</h2>
            <p>{{ ucwords($application->job?->title ?? '—') }} · Applied {{ $application->created_at->timezone($global->timezone)->format('d M, Y') }}</p>
        </div>

        <div class="ja-header-pills">
            <span class="ja-pill ja-pill-stage" style="background: {{ $stagePillBg }};">
                {{ ucwords($application->status->status) }}
            </span>
            <span class="ja-pill ja-pill-cat {{ $detailCatClass }}">{{ ucfirst($detailCatName) }}</span>
        </div>

        {{-- ── PREV / NEXT NAVIGATION ── --}}
        <div class="ja-nav-arrows" id="ja-nav-arrows">
            <button type="button"
                    class="ja-nav-btn"
                    id="ja-prev-btn"
                    onclick="jaNavigate('prev', {{ $application->id }})"
                    title="Previous applicant"
                    disabled>
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <span class="ja-nav-counter" id="ja-nav-counter">—</span>
            <button type="button"
                    class="ja-nav-btn"
                    id="ja-next-btn"
                    onclick="jaNavigate('next', {{ $application->id }})"
                    title="Next applicant"
                    disabled>
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <button type="button" class="right-side-toggle ja-close-btn" title="@lang('app.close')" aria-label="@lang('app.close')">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── BODY: two columns ── --}}
    <div class="ja-body">

        {{-- ──────── LEFT: RESUME VIEWER ──────── --}}
        <div class="ja-pdf-panel">

            <div class="ja-pdf-toolbar">
                <div class="ja-pdf-toolbar-label">
                    <i class="fa fa-file-pdf-o"></i>
                    <span>@lang('modules.jobApplication.resume')</span>
                </div>

                @if($resumeUrl)
                    <div class="ja-pdf-toolbar-actions">
                        <a href="{{ $resumeUrl }}" target="_blank" class="ja-pdf-btn">
                            <i class="fa fa-external-link"></i>
                            View
                        </a>
                        <a href="{{ $resumeUrl }}" download class="ja-pdf-btn ja-pdf-btn-primary">
                            <i class="fa fa-download"></i>
                            Download
                        </a>
                    </div>
                @endif
            </div>

            @if($resumeUrl)
                <embed
                    src="{{ $resumeUrl }}"
                    type="application/pdf"
                    class="ja-pdf-frame">
            @else
                <div class="ja-pdf-no-resume">
                    <i class="fa fa-file-pdf-o"></i>
                    <p>No resume uploaded.</p>
                </div>
            @endif

        </div>
        
        {{-- ──────── RIGHT: DETAIL TABS ──────── --}}
        <div class="ja-right-panel">

            {{-- Tabs --}}
            <div class="ja-tabs">
                <div class="ja-tab active" data-tab="details">
                    <i class="fa fa-user" style="font-size:11px"></i>
                    @lang('app.details')
                </div>
                <div class="ja-tab" data-tab="notes">
                    <i class="fa fa-sticky-note-o" style="font-size:11px"></i>
                    @lang('modules.jobApplication.applicantNotes')
                    @if($application->notes->count() > 0)
                        <span class="ja-tab-badge">{{ $application->notes->count() }}</span>
                    @endif
                </div>
                <div class="ja-tab" data-tab="client-notes">
                    <i class="fa fa-building" style="font-size:11px"></i>
                    Client Notes
                    @if($clientNotes->count() > 0)
                        <span class="ja-tab-badge">{{ $clientNotes->count() }}</span>
                    @endif
                </div>
                @if(count($answers) > 0)
                <div class="ja-tab" data-tab="qa">
                    <i class="fa fa-question-circle-o" style="font-size:11px"></i>
                    @lang('modules.front.additionalDetails')
                </div>
                @endif
                @if(!is_null($application->schedule))
                <div class="ja-tab" data-tab="schedule">
                    <i class="fa fa-calendar" style="font-size:11px"></i>
                    @lang('modules.interviewSchedule.scheduleDetail')
                </div>
                @endif

                @if($previousApps->isNotEmpty())
                <div class="ja-tab" data-tab="history">
                    <i class="fa fa-history" style="font-size:11px"></i>
                    History
                    <span class="ja-tab-badge">{{ $previousApps->count() }}</span>
                </div>
                @endif
            </div>

            <div class="ja-right-scroll">
                @if($previousApps->isNotEmpty())
                <div id="ja-tab-history" class="ja-tab-pane" style="display:none">

                    @foreach($previousApps as $prev)
                    <div class="ja-card" style="margin-bottom:10px">

                        {{-- ── Job title + date + status ── --}}
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid #F0EEE9">
                            <div style="flex:1;min-width:0">
                                <div style="font-size:13.5px;font-weight:700;color:#1A1E2E;margin-bottom:3px">
                                    {{ ucwords($prev->job?->title ?? '—') }}
                                </div>
                                <div style="font-size:11px;color:#8A94A6;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                    <span><i class="fa fa-calendar-o" style="font-size:10px;margin-right:3px"></i>{{ $prev->created_at?->format('d M Y') }}</span>
                                    @if($prev->location)
                                        <span><i class="fa fa-map-marker" style="font-size:10px;margin-right:3px"></i>{{ ucwords($prev->location->location) }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold text-white shrink-0"
                                style="background:{{ $prev->status?->color ?? '#6B7280' }}">
                                {{ ucwords(str_replace('_', ' ', $prev->status?->status ?? '—')) }}
                            </span>
                        </div>

                        {{-- ── Cover letter snippet ── --}}
                        @if($prev->cover_letter)
                        <div style="margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #F0EEE9">
                            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#B0B8C4;margin-bottom:5px">
                                <i class="fa fa-file-text-o" style="font-size:10px"></i> Cover Letter
                            </div>
                            <p style="font-size:12px;color:#5A6478;line-height:1.6;margin:0;
                                display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden">
                                {{ $prev->cover_letter }}
                            </p>
                        </div>
                        @endif

                        {{-- ── Skills ── --}}
                        @php
                            $prevSkillIds = is_array($prev->skills) ? $prev->skills : [];
                            $prevSkillNames = \App\Skill::whereIn('id', $prevSkillIds)->pluck('name');
                        @endphp
                        @if($prevSkillNames->isNotEmpty())
                        <div style="margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #F0EEE9">
                            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#B0B8C4;margin-bottom:6px">
                                <i class="fa fa-star" style="font-size:10px"></i> Skills
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:5px">
                                @foreach($prevSkillNames as $skillName)
                                <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:500;background:#F1F3F7;color:#5A6478;border:1px solid #E2DED8">
                                    {{ $skillName }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- ── Q&A answers ── --}}
                        @php
                            $prevAnswers = \App\JobApplicationAnswer::where('job_application_id', $prev->id)
                                ->with('question')
                                ->whereNotNull('answer')
                                ->get()
                                ->filter(fn($a) => !empty(trim($a->answer)));
                        @endphp
                        @if($prevAnswers->isNotEmpty())
                        <div style="margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #F0EEE9">
                            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#B0B8C4;margin-bottom:6px">
                                <i class="fa fa-question-circle-o" style="font-size:10px"></i> Screening Answers
                            </div>
                            @foreach($prevAnswers as $ans)
                            <div style="margin-bottom:6px">
                                <div style="font-size:11.5px;font-weight:600;color:#1A1E2E;margin-bottom:2px">
                                    {{ $ans->question?->question ?? '—' }}
                                </div>
                                <div style="font-size:12px;color:#5A6478">
                                    @if(strtolower(trim($ans->answer)) === 'yes')
                                        <span style="display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;background:#ECFDF5;color:#065F46">
                                            <i class="fa fa-check" style="font-size:9px;margin-right:4px"></i> Yes
                                        </span>
                                    @elseif(strtolower(trim($ans->answer)) === 'no')
                                        <span style="display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;background:#FEF2F2;color:#991B1B">
                                            <i class="fa fa-times" style="font-size:9px;margin-right:4px"></i> No
                                        </span>
                                    @else
                                        {{ ucfirst($ans->answer) }}
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- ── Notes ── --}}
                        @php
                            $prevNotes = \App\ApplicantNote::where('job_application_id', $prev->id)
                                ->with('user:id,name')
                                ->orderByDesc('created_at')
                                ->get();
                        @endphp
                        @if($prevNotes->isNotEmpty())
                        <div>
                            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#B0B8C4;margin-bottom:6px">
                                <i class="fa fa-sticky-note-o" style="font-size:10px"></i> Notes
                                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:16px;height:16px;padding:0 4px;border-radius:20px;background:#F0EEE9;font-size:10px;color:#8A94A6;margin-left:4px">
                                    {{ $prevNotes->count() }}
                                </span>
                            </div>
                            @foreach($prevNotes as $pNote)
                            <div style="background:#F8F7F4;border-radius:8px;border-left:3px solid #2563EB;padding:8px 11px;margin-bottom:6px">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                                    <span style="font-size:11.5px;font-weight:600;color:#1A1E2E">
                                        <i class="fa fa-user-circle" style="font-size:12px;color:#2563EB;margin-right:4px"></i>
                                        {{ ucwords($pNote->user?->name ?? '—') }}
                                    </span>
                                    <span style="font-size:10.5px;color:#B0B8C4">
                                        {{ $pNote->created_at?->format('d M Y') }}
                                    </span>
                                </div>
                                <p style="font-size:12px;color:#5A6478;line-height:1.6;margin:0">
                                    {{ ucfirst($pNote->note_text) }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- ── No extra info fallback ── --}}
                        @if(!$prev->cover_letter && $prevSkillNames->isEmpty() && $prevAnswers->isEmpty() && $prevNotes->isEmpty())
                        <div style="font-size:12px;color:#B0B8C4;text-align:center;padding:8px 0">
                            <i class="fa fa-info-circle" style="margin-right:5px"></i> No additional details recorded.
                        </div>
                        @endif

                    </div>
                    @endforeach

                </div>
                @endif
                {{-- ── DETAILS TAB ── --}}
                <div id="ja-tab-details" class="ja-tab-pane">

                    {{-- Quick actions --}}
                    <div class="ja-card">
                        <div class="ja-action-btns">
                            @if($user->cans('add_schedule') && $application->status->status == 'interview' && is_null($application->schedule))
                            <a onclick="createSchedule('{{ $application->id }}')" href="javascript:;" class="ja-btn ja-btn-blue">
                                <i class="fa fa-calendar-plus-o"></i>
                                @lang('modules.interviewSchedule.scheduleInterview')
                            </a>
                            @endif
                            @if($application->status->status == 'hired' && is_null($application->onboard))
                            <a href="{{ route('admin.job-onboard.create') }}?id={{ $application->id }}" class="ja-btn ja-btn-green">
                                <i class="fa fa-rocket"></i>
                                @lang('app.startOnboard')
                            </a>
                            @endif
                        </div>

                        {{-- Stage mover --}}
                        @if($user->cans('edit_job_applications'))
                        <div style="margin-bottom:14px">
                            <div class="ja-card-title" style="margin-bottom:8px">
                                <i class="fa fa-exchange" style="font-size:11px"></i> Move to Stage
                            </div>
                            <div class="ja-stage-row">
                                <span class="ja-current-badge" style="background: {{ $currentStatus?->color ?? '#6B7280' }};">
                                    <i class="fa fa-check" style="font-size:9px"></i>
                                    {{ ucwords(str_replace('_', ' ', $currentStatus?->status ?? '')) }}
                                </span>
                                <i class="fa fa-long-arrow-right" style="color:#B0B8C4;font-size:14px;flex-shrink:0"></i>
                                <select class="ja-stage-select" id="stage-mover-select-{{ $application->id }}"
                                    onchange="jaMoveFromDetail({{ $application->id }}, this.value, this.options[this.selectedIndex].text, {{ $currentStatusId }})">
                                    <option value="">Select stage…</option>
                                    @foreach($allStatuses as $stageOption)
                                        @if($stageOption->id !== $currentStatusId)
                                        <option value="{{ $stageOption->id }}">{{ ucwords(str_replace('_', ' ', $stageOption->status)) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Rating --}}
                        <!-- <div style="border-top:1px solid #F0EEE9;padding-top:12px">
                            <div class="ja-card-title" style="margin-bottom:8px">
                                <i class="fa fa-star-o" style="font-size:11px"></i> @lang('modules.jobApplication.ratingLabel')
                            </div>
                            <div class="ja-stars" id="stars-wrapper-{{ $application->id }}">
                                @for($s = 1; $s <= 5; $s++)
                                <i class="fa {{ $s <= ($application->rating ?? 0) ? 'fa-star' : 'fa-star-o' }} ja-star {{ $s <= ($application->rating ?? 0) ? 'on' : '' }}"
                                   data-val="{{ $s }}" onclick="jaSetRating({{ $application->id }}, {{ $s }})"
                                   style="font-size:22px;cursor:pointer;color:{{ $s <= ($application->rating ?? 0) ? '#F59E0B' : '#D1D5DB' }}"></i>
                                @endfor
                            </div>
                        </div> -->
                        @endif
                    </div>

                    {{-- Personal info --}}
                    <div class="ja-card">
                        <div class="ja-card-title">
                            <i class="fa fa-user" style="font-size:11px"></i> @lang('Personal Information')
                        </div>
                        <div class="ja-info-row">
                            <span class="ja-info-label"><i class="fa fa-id-card-o" style="font-size:11px"></i> @lang('app.name')</span>
                            <span class="ja-info-val">{{ ucwords($application->full_name) }}</span>
                        </div>
                        <div class="ja-info-row">
                            <span class="ja-info-label"><i class="fa fa-envelope-o" style="font-size:11px"></i> @lang('app.email')</span>
                            <span class="ja-info-val"><a href="mailto:{{ $application->email }}">{{ $application->email }}</a></span>
                        </div>
                        <div class="ja-info-row">
                            <span class="ja-info-label"><i class="fa fa-phone" style="font-size:11px"></i> @lang('app.phone')</span>
                            <span class="ja-info-val"><a href="tel:{{ $application->phone }}">{{ $application->phone }}</a></span>
                        </div>
                        @if (!is_null($application->gender))
                        <div class="ja-info-row">
                            <span class="ja-info-label"><i class="fa fa-venus-mars" style="font-size:11px"></i> @lang('app.gender')</span>
                            <span class="ja-info-val">{{ ucfirst($application->gender) }}</span>
                        </div>
                        @endif
                        @if (!is_null($application->dob))
                        <div class="ja-info-row">
                            <span class="ja-info-label"><i class="fa fa-birthday-cake" style="font-size:11px"></i> @lang('app.dob')</span>
                            <span class="ja-info-val">{{ $application->dob->format('jS F, Y') }}</span>
                        </div>
                        @endif
                        <div class="ja-info-row">
                            <span class="ja-info-label"><i class="fa fa-briefcase" style="font-size:11px"></i> @lang('modules.jobApplication.appliedFor')</span>
                            <span class="ja-info-val">{{ ucwords($application->job?->title ?? 'N/A') }} ({{ ucwords(optional($application->location)->location ?? 'N/A') }})</span>
                        </div>
                        @if (!is_null($application->city))
                        <div class="ja-info-row">
                            <span class="ja-info-label"><i class="fa fa-map-marker" style="font-size:11px"></i> @lang('app.city')</span>
                            <span class="ja-info-val">{{ $application->city }}{{ $application->state ? ', '.$application->state : '' }}{{ $application->country ? ', '.$application->country : '' }}</span>
                        </div>
                        @endif
                        @if (!is_null($application->address))
                        <div class="ja-info-row">
                            <span class="ja-info-label"><i class="fa fa-home" style="font-size:11px"></i> @lang('app.address')</span>
                            <span class="ja-info-val">{{ $application->address }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Cover letter --}}
                    @if (!is_null($application->cover_letter))
                    <div class="ja-card">
                        <div class="ja-card-title">
                            <i class="fa fa-file-text-o" style="font-size:11px"></i> @lang('modules.jobs.coverLetter')
                        </div>
                        <p style="font-size:12.5px;color:#5A6478;line-height:1.7;white-space:pre-wrap;margin:0">{{ $application->cover_letter }}</p>
                    </div>
                    @endif

                    {{-- Skills --}}
                    @if ($user->cans('edit_job_applications'))
             
                    <div class="ja-card" id="skills-container">
                        <div class="ja-card-title">
                            <i class="fa fa-star" style="font-size:11px"></i> @lang('modules.jobApplication.skills')
                        </div>

                        {{-- Select2 multi-select --}}
                        <div class="mb-3">
                            <select name="skills[]" id="skills" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm select2" multiple>
                                @forelse ($skills as $skill)
                                    <option @if(!is_null($application->skills) && in_array($skill->id, $application->skills)) selected @endif value="{{ $skill->id }}">{{ $skill->name }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>

                        {{-- ── Parse from CV ── --}}
                        <div style="border:1px solid #E8E6E1;border-radius:10px;padding:12px;margin-bottom:10px;background:#F8F7F4">
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#B0B8C4;margin-bottom:8px">
                                <i class="fa fa-magic" style="font-size:11px"></i> Auto-detect from CV
                            </div>

                            <button type="button" id="parse-skills-btn-{{ $application->id }}"
                                    onclick="jaParseSkills({{ $application->id }})"
                                    class="ja-btn ja-btn-blue" style="min-width:auto;flex:none;display:inline-flex;margin-bottom:8px">
                                <i class="fa fa-search" id="parse-icon-{{ $application->id }}"></i>
                                <span id="parse-label-{{ $application->id }}">Parse skills from CV</span>
                            </button>

                            {{-- Results area --}}
                            <div id="parse-result-{{ $application->id }}" style="display:none">

                                {{-- Matched chips (exist in DB — click to select) --}}
                                <div id="parse-matched-wrap-{{ $application->id }}" style="display:none;margin-bottom:8px">
                                    <div style="font-size:11px;color:#065F46;font-weight:600;margin-bottom:5px">
                                        <i class="fa fa-check-circle" style="font-size:11px"></i> Matched in your skills list — click to add:
                                    </div>
                                    <div id="parse-matched-chips-{{ $application->id }}" style="display:flex;flex-wrap:wrap;gap:5px"></div>
                                </div>

                                {{-- New chips (not in DB — click to create & add) --}}
                                <div id="parse-new-wrap-{{ $application->id }}" style="display:none">
                                    <div style="font-size:11px;color:#92400E;font-weight:600;margin-bottom:5px">
                                        <i class="fa fa-plus-circle" style="font-size:11px"></i> New skills found — click to create & add:
                                    </div>
                                    <div id="parse-new-chips-{{ $application->id }}" style="display:flex;flex-wrap:wrap;gap:5px"></div>
                                </div>

                                <div id="parse-status-{{ $application->id }}" style="font-size:11.5px;color:#059669;margin-top:6px"></div>
                            </div>

                            {{-- Error --}}
                            <div id="parse-error-{{ $application->id }}" style="display:none;font-size:12px;color:#EF4444;margin-top:6px">
                                <i class="fa fa-exclamation-circle"></i> <span></span>
                            </div>
                        </div>

                        {{-- ── Add Manually ── --}}
                        <div style="border:1px solid #E8E6E1;border-radius:10px;padding:12px;margin-bottom:12px;background:#F8F7F4">
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#B0B8C4;margin-bottom:8px">
                                <i class="fa fa-keyboard-o" style="font-size:11px"></i> Add manually
                            </div>
                            <div style="display:flex;gap:7px;align-items:center">
                                <input type="text"
                                    id="manual-skill-input-{{ $application->id }}"
                                    placeholder="Type a skill name…"
                                    class="ja-note-textarea"
                                    style="flex:1;resize:none;height:36px;padding:6px 10px;font-size:12.5px"
                                    onkeydown="if(event.key==='Enter'){event.preventDefault();jaAddManualSkill({{ $application->id }});}">
                                <button type="button"
                                        onclick="jaAddManualSkill({{ $application->id }})"
                                        class="ja-btn ja-btn-blue"
                                        style="min-width:auto;flex:none;display:inline-flex;white-space:nowrap;height:36px;padding:0 12px">
                                    <i class="fa fa-plus"></i> Add
                                </button>
                            </div>
                        </div>

                        {{-- Save button --}}
                        <a href="javascript:addSkills({{ $application->id }});" id="add-skills"
                        class="ja-btn ja-btn-blue" style="min-width:auto;flex:none;display:inline-flex">
                            <i class="fa fa-plus"></i>
                            @if (!is_null($application->skills) && sizeof($application->skills) > 0)
                                @lang('modules.jobApplication.updateSkills')
                            @else
                                @lang('modules.jobApplication.addSkills')
                            @endif
                        </a>
                    </div>

                    {{-- ── Inline JS for skills panel ── --}}
                    <script>
                    (function () {

                        /* ── Parse skills from CV ── */
                        window.jaParseSkills = function (appId) {
                            var btn   = document.getElementById('parse-skills-btn-' + appId);
                            var icon  = document.getElementById('parse-icon-' + appId);
                            var label = document.getElementById('parse-label-' + appId);
                            var resArea  = document.getElementById('parse-result-' + appId);
                            var errArea  = document.getElementById('parse-error-' + appId);
                            var status   = document.getElementById('parse-status-' + appId);

                            // Loading state
                            btn.disabled = true;
                            icon.className  = 'fa fa-spinner fa-spin';
                            label.textContent = 'Parsing…';
                            resArea.style.display = 'none';
                            errArea.style.display = 'none';

                            $.ajax({
                                type: 'POST',
                                url: '{{ route("admin.job-applications.parse-skills", ":id") }}'.replace(':id', appId),
                                data: { _token: '{{ csrf_token() }}' },
                                success: function (res) {
                                    btn.disabled = false;
                                    icon.className  = 'fa fa-search';
                                    label.textContent = 'Re-parse CV';

                                    if (res.status !== 'success') {
                                        errArea.style.display = 'block';
                                        errArea.querySelector('span').textContent = res.message || 'Parsing failed.';
                                        return;
                                    }

                                    resArea.style.display = 'block';
                                    var addedCount = 0;

                                    /* ── Matched chips ── */
                                    var matchedWrap  = document.getElementById('parse-matched-wrap-' + appId);
                                    var matchedChips = document.getElementById('parse-matched-chips-' + appId);
                                    matchedChips.innerHTML = '';

                                    if (res.matched_skills && res.matched_skills.length) {
                                        matchedWrap.style.display = 'block';
                                        res.matched_skills.forEach(function (skill) {
                                            var chip = document.createElement('span');
                                            chip.style.cssText = 'display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:500;cursor:pointer;background:#ECFDF5;color:#065F46;border:1px dashed #6EE7B7;transition:all .15s';
                                            chip.innerHTML = '<i class="fa fa-plus" style="font-size:9px"></i> ' + skill.name;
                                            chip.onclick = function () {
                                                var $sel = $('#skills');
                                                if ($sel.val() && $sel.val().indexOf(String(skill.id)) > -1) return;
                                                var opt = $sel.find('option[value="' + skill.id + '"]');
                                                if (opt.length) {
                                                    opt.prop('selected', true);
                                                    $sel.trigger('change');
                                                } else {
                                                    var newOpt = new Option(skill.name, skill.id, true, true);
                                                    $sel.append(newOpt).trigger('change');
                                                }
                                                chip.style.borderStyle = 'solid';
                                                chip.style.background  = '#D1FAE5';
                                                chip.innerHTML = '<i class="fa fa-check" style="font-size:9px"></i> ' + skill.name;
                                                chip.onclick = null;
                                                chip.style.cursor = 'default';
                                                addedCount++;
                                                status.textContent = '<i class="fa fa-spinner fa-spin"></i> Saving…';
                                                addSkills(appId, function() {
                                                    status.textContent = 'Saved successfully';
                                                });
                                            };
                                            matchedChips.appendChild(chip);
                                        });
                                    } else {
                                        matchedWrap.style.display = 'none';
                                    }

                                    /* ── New chips ── */
                                    var newWrap  = document.getElementById('parse-new-wrap-' + appId);
                                    var newChips = document.getElementById('parse-new-chips-' + appId);
                                    newChips.innerHTML = '';

                                    if (res.new_skills && res.new_skills.length) {
                                        newWrap.style.display = 'block';
                                        res.new_skills.forEach(function (name) {
                                            var chip = document.createElement('span');
                                            chip.style.cssText = 'display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:500;cursor:pointer;background:#FFFBEB;color:#92400E;border:1px dashed #FDE68A;transition:all .15s';
                                            chip.innerHTML = '<i class="fa fa-plus" style="font-size:9px"></i> ' + name;
                                            chip.onclick = function () {
                                                // Create skill via AJAX then add to Select2
                                                $.ajax({
                                                    type: 'POST',
                                                    url: '{{ route("admin.skills.quick-create") }}', // adjust to your actual route
                                                    data: { _token: '{{ csrf_token() }}', name: name },
                                                    success: function (r) {
                                                        if (r.status === 'success' && r.id) {
                                                            var newOpt = new Option(name, r.id, true, true);
                                                            $('#skills').append(newOpt).trigger('change');
                                                            chip.style.background  = '#FEF3C7';
                                                            chip.style.borderStyle = 'solid';
                                                            chip.innerHTML = '<i class="fa fa-check" style="font-size:9px"></i> ' + name;
                                                            chip.onclick = null;
                                                            chip.style.cursor = 'default';
                                                            addedCount++;
                                                            status.textContent = '<i class="fa fa-spinner fa-spin"></i> Saving…';
                                                            addSkills(appId, function() {
                                                                status.textContent = 'Saved successfully.';
                                                            });}
                                                    },
                                                    error: function () {
                                                        // Skill create failed — add as temp option without DB ID
                                                        // so it shows in the select but won't persist until you handle it
                                                        chip.style.opacity = '.4';
                                                        chip.title = 'Could not create skill — check permissions.';
                                                    }
                                                });
                                            };
                                            newChips.appendChild(chip);
                                        });
                                    } else {
                                        newWrap.style.display = 'none';
                                    }

                                    if (!res.matched_skills.length && !res.new_skills.length) {
                                        status.textContent = 'No skills detected in this CV.';
                                    }
                                },
                                error: function () {
                                    btn.disabled = false;
                                    icon.className  = 'fa fa-search';
                                    label.textContent = 'Parse skills from CV';
                                    errArea.style.display = 'block';
                                    errArea.querySelector('span').textContent = 'Server error. Please try again.';
                                }
                            });
                        };

                        /* ── Add skill manually ── */
                    window.jaAddManualSkill = function (appId) {
                        var input = document.getElementById('manual-skill-input-' + appId);
                        var name  = input.value.trim();
                        if (!name) return;

                        var $sel = $('#skills');

                        var exists = $sel.find('option').filter(function () {
                            return $(this).text().toLowerCase() === name.toLowerCase();
                        });

                        if (exists.length) {
                            exists.prop('selected', true);
                            $sel.trigger('change');
                            input.value = '';
                            addSkills(appId);  // auto-save
                            return;
                        }

                        var tempVal = 'new:' + name;
                        var newOpt  = new Option(name, tempVal, true, true);
                        $sel.append(newOpt).trigger('change');
                        input.value = '';
                        addSkills(appId);  // auto-save
                    };

                    })();
                    </script>
                    @endif
                </div>{{-- /details --}}

                {{-- ── NOTES TAB ── --}}
                <div id="ja-tab-notes" class="ja-tab-pane" style="display:none">
                    <div id="applicant-notes">
                        @foreach($application->notes as $note)
                        <div class="ja-note" id="note-{{ $note->id }}">
                            <div class="ja-note-meta">
                                <span class="ja-note-author">
                                    <i class="fa fa-user-circle" style="font-size:14px;color:#2563EB"></i>
                                    {{ ucwords($note->user->name) }}
                                </span>
                                <span class="ja-note-time">
                                    {{ $note->created_at->timezone('America/Toronto')->format('d M Y, h:i A') }}
                                </span>
                            </div>
                            <p class="note-text ja-note-body">{{ ucfirst($note->note_text) }}</p>
                            <div class="note-textarea"></div>
                            @if($user->cans('edit_job_applications'))
                            <div class="ja-note-actions">
                                <button class="edit-note ja-note-btn" data-note-id="{{ $note->id }}">
                                    <i class="fa fa-pencil"></i> @lang('app.edit')
                                </button>
                                <button class="delete-note ja-note-btn" data-note-id="{{ $note->id }}" style="color:#EF4444">
                                    <i class="fa fa-trash"></i> @lang('app.delete')
                                </button>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                
                    {{-- ── CLIENT NOTES TAB ── --}}
                    <div id="ja-tab-client-notes" class="ja-tab-pane" style="display:none;min-height:200px">

                        {{-- Shared-job context banner --}}
                        <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:10px 14px;margin-bottom:10px;display:flex;align-items:center;gap:8px">
                            <i class="fa fa-info-circle" style="color:#059669;font-size:13px;flex-shrink:0"></i>
                            <span style="font-size:11.5px;color:#065F46;line-height:1.5">
                                These notes are shared across <strong>all applicants</strong> for
                                <strong>{{ ucwords($application->job?->title ?? 'this job') }}</strong>.
                            </span>
                        </div>

                        {{-- Notes list --}}
                        <div id="client-notes-list">
                            @include('admin.job-applications.partials.client-notes-list', ['clientNotes' => $clientNotes])
                        </div>

                        {{-- Add note form — always visible for now to debug --}}
                        <div class="ja-add-note" style="margin-top:10px">
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#B0B8C4;margin-bottom:8px">
                                <i class="fa fa-plus-circle" style="color:#059669"></i> Add Client Note
                            </div>
                            <textarea id="client_note_text"
                                    rows="4"
                                    class="ja-note-textarea"
                                    style="width:100%;min-height:90px"
                                    placeholder="Add a note visible to all applicants on this job…"></textarea>
                            <button id="add-client-note"
                                    class="ja-save-note-btn"
                                    style="background:#059669;margin-top:8px"
                                    data-job-id="{{ $application->job_id }}">
                                <i class="fa fa-plus"></i> Add Client Note
                            </button>
                        </div>

                    </div>
                                        @if($user->cans('edit_job_applications'))
                    <div class="ja-add-note" style="margin-top:4px">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#B0B8C4;margin-bottom:8px">
                            @lang('modules.jobApplication.addNote')
                        </div>
                        <textarea id="note_text" rows="3" class="ja-note-textarea"
                                  placeholder="@lang('modules.jobApplication.addNote')"></textarea>
                        <button id="add-note" class="ja-save-note-btn">
                            <i class="fa fa-plus"></i> @lang('modules.jobApplication.addNote')
                        </button>
                    </div>
                    @endif
                </div>{{-- /notes --}}

                {{-- ── Q&A TAB ── --}}
                @if(count($answers) > 0)
                <div id="ja-tab-qa" class="ja-tab-pane" style="display:none">
                    @forelse($answers as $answer)
                    <div class="ja-qa-item">
                        <div class="ja-qa-q">{{ $answer->question->question }}</div>
                        @if($answer->question->type == 'text')
                            <span style="font-size:12.5px;color:#5A6478">{{ ucfirst($answer->answer) }}</span>
                        @elseif($answer->question->type == 'radio')
                            <span class="ja-qa-badge {{ strtolower($answer->answer) == 'yes' ? 'ja-qa-yes' : (strtolower($answer->answer) == 'no' ? 'ja-qa-no' : 'ja-qa-yes') }}">
                                <i class="fa fa-circle" style="font-size:8px;margin-right:4px"></i>
                                {{ ucfirst($answer->answer) }}
                            </span>
                        @else
                            @if(!is_null($answer->file))
                            <a target="_blank" href="{{ $answer->file_url }}"
                               class="ja-btn ja-btn-blue" style="display:inline-flex;flex:none;min-width:auto;margin-top:4px">
                                <i class="fa fa-file-o"></i> @lang('app.view') @lang('app.file')
                            </a>
                            @endif
                        @endif
                    </div>
                    @empty
                    @endforelse
                </div>
                @endif

                {{-- ── SCHEDULE TAB ── --}}
                @if(!is_null($application->schedule))
                <div id="ja-tab-schedule" class="ja-tab-pane" style="display:none">
                    <div class="ja-card">
                        <div class="ja-card-title">
                            <i class="fa fa-calendar-check-o" style="font-size:11px"></i> @lang('modules.interviewSchedule.scheduleDetail')
                        </div>
                        <div class="ja-schedule-card" style="margin-bottom:14px">
                            <div class="ja-schedule-date">
                                <i class="fa fa-clock-o" style="font-size:13px"></i>
                                {{ $application->schedule->schedule_date->format('d M, Y \a\t H:i') }}
                            </div>
                            @if($zoom_setting->enable_zoom == 1)
                            <div class="ja-schedule-type">
                                <i class="fa {{ $application->schedule->interview_type == 'online' ? 'fa-video-camera' : 'fa-building' }}" style="margin-right:5px"></i>
                                {{ $application->schedule->interview_type == 'online' ? 'Online' : 'Offline' }}
                            </div>
                            @endif
                        </div>

                        @if(count($application->schedule->employee) > 0)
                        <div class="ja-card-title" style="margin-bottom:10px">
                            <i class="fa fa-users" style="font-size:11px"></i> @lang('modules.interviewSchedule.assignedEmployee')
                        </div>
                        @foreach($application->schedule->employee as $emp)
                        <div class="ja-interviewer-row" style="margin-bottom:6px">
                            <div style="display:flex;align-items:center;gap:9px">
                                <div class="ja-interviewer-avatar">
                                    {{ strtoupper(substr($emp->user->name, 0, 2)) }}
                                </div>
                                <span style="font-size:12.5px;font-weight:600;color:#1A1E2E">{{ ucwords($emp->user->name) }}</span>
                            </div>
                            <span class="ja-small-badge
                                {{ $emp->user_accept_status == 'accept' ? 'ja-badge-accept' :
                                  ($emp->user_accept_status == 'refuse' ? 'ja-badge-refuse' : 'ja-badge-pending') }}">
                                {{ ucwords($emp->user_accept_status) }}
                            </span>
                        </div>
                        @endforeach
                        @endif
                    </div>

                    @if(isset($application->schedule->comments) && count($application->schedule->comments) > 0)
                    <div class="ja-card">
                        <div class="ja-card-title"><i class="fa fa-comments" style="font-size:11px"></i> @lang('modules.interviewSchedule.comments')</div>
                        @foreach($application->schedule->comments as $comment)
                        <div class="ja-note" style="border-left-color:#059669;margin-bottom:8px">
                            <div class="ja-note-meta">
                                <span class="ja-note-author">{{ $comment->user->name }}</span>
                            </div>
                            <p class="ja-note-body">{{ $comment->comment }}</p>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

            </div>{{-- /right-scroll --}}
        </div>{{-- /right-panel --}}

    </div>{{-- /body --}}
</div>

{{-- ── Scripts ── --}}
@if($user->cans('edit_job_applications'))
<script src="{{ asset('assets/plugins/jquery-bar-rating-master/dist/jquery.barrating.min.js') }}" type="text/javascript"></script>
@endif

<script>
/* ── Tab switching ── */
document.querySelectorAll('.ja-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        var target = this.dataset.tab;
        document.querySelectorAll('.ja-tab').forEach(function(t) { t.classList.remove('active'); });
        document.querySelectorAll('.ja-tab-pane').forEach(function(p) { p.style.display = 'none'; });
        this.classList.add('active');
        var pane = document.getElementById('ja-tab-' + target);
        if (pane) pane.style.display = 'block';
    });
});

/* ── Stage mover ── */
function jaMoveFromDetail(appId, toStatusId, toStatusLabel, currentStatusId) {
    if (!toStatusId) return;

    var token = '{{ csrf_token() }}';
    var url = '{{ route("admin.job-applications.bulk-status-update") }}';

    $.easyAjax({
        type: 'POST',
        url: url,
        data: {
            _token: token,
            ids: [appId],
            status_id: toStatusId
        },
        success: function(response) {
            if (response.status === 'success') {

                var showUrl = "{{ route('admin.job-applications.show', ':id') }}"
                    .replace(':id', appId);

                $.easyAjax({
                    type: 'GET',
                    url: showUrl,
                    success: function(res) {
                        if (res.status === 'success') {
                            $('#right-sidebar-content').html(res.view);
                        }
                    }
                });

                if (typeof table !== 'undefined') {
                    table.draw(false);
                }

                if (typeof jaLoadTabCounts === 'function') {
                    jaLoadTabCounts();
                }
            }
        }
    });
}

/* ── Rating ── */
function jaSetRating(appId, val) {
    document.querySelectorAll('#stars-wrapper-' + appId + ' i').forEach(function(s, i) {
        s.style.color = i < val ? '#F59E0B' : '#D1D5DB';
    });
    var url = "{{ route('admin.job-applications.rating-save',':id') }}".replace(':id', appId);
    $.easyAjax({
        type: 'POST', url: url,
        data: { rating: val, '_token': '{{ csrf_token() }}' }
    });
}

/* ── Skills ── */
$('.select2#skills').select2();
function addSkills(applicationId, callback) {
    var url = "{{ route('admin.job-applications.addSkills', ':id') }}".replace(':id', applicationId);
    $.easyAjax({
        url: url, type: 'POST', container: '#skills-container',
        data: { _token: '{{ csrf_token() }}', skills: $('#skills').val() },
        success: function(response) {
            if (response.status === 'success') {
                if (typeof callback === 'function') callback();
                if (typeof table !== 'undefined') table.draw(false); else loadData();
            }
        }
    });
}
/* ── Notes ── */
$('#add-note').click(function() {
    $.easyAjax({
        type: 'POST', url: "{{ route('admin.applicant-note.store') }}",
        data: { '_token': '{{ csrf_token() }}', 'id': {{ $application->id }}, 'note': $('#note_text').val() },
        success: function(response) {
            if (response.status == 'success') {
                $('#applicant-notes').html(response.view);
                $('#note_text').val('');
            }
        }
    });
});

$('body').on('click', '.edit-note', function() {
    $(this).hide();
    var noteId = $(this).data('note-id');
    $('body').find('#note-' + noteId + ' .note-text').hide();
    var noteText = $('body').find('#note-' + noteId + ' .note-text').html();
    var textArea = '<textarea id="edit-note-text-' + noteId + '" class="ja-note-textarea" rows="3">' + noteText + '</textarea>' +
        '<button class="update-note ja-save-note-btn" data-note-id="' + noteId + '" style="margin-top:6px"><i class="fa fa-check"></i> @lang("app.save")</button>';
    $('body').find('#note-' + noteId + ' .note-textarea').html(textArea);
});

$('body').on('click', '.update-note', function() {
    var noteId = $(this).data('note-id');
    var url = "{{ route('admin.applicant-note.update', ':id') }}".replace(':id', noteId);
    $.easyAjax({
        type: 'POST', url: url,
        data: { '_token': '{{ csrf_token() }}', 'noteId': noteId, 'note': $('#edit-note-text-' + noteId).val(), '_method': 'PUT' },
        success: function(response) {
            if (response.status == 'success') $('#applicant-notes').html(response.view);
        }
    });
});

$('body').on('click', '.delete-note', function() {
    var noteId = $(this).data('note-id');
    swal({
        title: "@lang('errors.areYouSure')", text: "@lang('errors.deleteWarning')", type: "warning",
        showCancelButton: true, confirmButtonColor: "#DD6B55", confirmButtonText: "@lang('app.delete')",
        cancelButtonText: "@lang('app.cancel')", closeOnConfirm: true, closeOnCancel: true
    }, function(isConfirm) {
        if (isConfirm) {
            var url = "{{ route('admin.applicant-note.destroy', ':id') }}".replace(':id', noteId);
            $.easyAjax({
                type: 'POST', url: url,
                data: { '_token': '{{ csrf_token() }}', '_method': 'DELETE' },
                success: function(response) {
                    if (response.status == 'success') $('#applicant-notes').html(response.view);
                }
            });
        }
    });
});

function deleteApplication(applicationId) {
    swal({
        title: "@lang('errors.areYouSure')", text: "@lang('errors.deleteWarning')", type: "warning",
        showCancelButton: true, confirmButtonColor: "#DD6B55", confirmButtonText: "@lang('app.delete')",
        cancelButtonText: "@lang('app.cancel')", closeOnConfirm: true, closeOnCancel: true
    }, function(isConfirm) {
        if (isConfirm) {
            var url = "{{ route('admin.job-applications.destroy', ':id') }}".replace(':id', applicationId);
            $.easyAjax({
                type: 'POST', url: url,
                data: { '_token': '{{ csrf_token() }}', '_method': 'DELETE' },
                success: function(response) {
                    if (window.raCloseRightSidebar) window.raCloseRightSidebar();
                    if (response.status === 'success') {
                        if (typeof table !== 'undefined') table.draw(false); else loadData();
                    }
                }
            });
        }
    });
}

/* ══════════════════════════════════════════════
   PREV / NEXT APPLICANT NAVIGATION
   jaApplicantIds is built in index.blade.php
   inside drawCallback via jaRebuildIds().
   ══════════════════════════════════════════════ */
(function () {

    var CURRENT_ID = {{ $application->id }};
    var showUrlTpl = "{{ route('admin.job-applications.show', ':id') }}";

    /* ── Helpers ── */
    function getIds() {
        return (typeof jaApplicantIds !== 'undefined' && Array.isArray(jaApplicantIds) && jaApplicantIds.length)
            ? jaApplicantIds
            : [];
    }

    function currentIndex() {
        return getIds().indexOf(CURRENT_ID);
    }

    function updateNavUI() {
        var ids  = getIds();
        var idx  = currentIndex();
        var prev = document.getElementById('ja-prev-btn');
        var next = document.getElementById('ja-next-btn');
        var ctr  = document.getElementById('ja-nav-counter');

        if (!ids.length || idx === -1) {
            if (ctr) ctr.textContent = '—';
            if (prev) prev.disabled = true;
            if (next) next.disabled = true;
            return;
        }

        if (ctr)  ctr.textContent  = (idx + 1) + ' / ' + ids.length;
        if (prev) prev.disabled    = (idx === 0);
        if (next) next.disabled    = (idx === ids.length - 1);
    }

    /* ── Public navigate function ── */
    window.jaNavigate = function (direction, fromId) {
        var ids = getIds();
        var idx = ids.indexOf(fromId);

        if (idx === -1 || !ids.length) return;

        var targetId = (direction === 'prev') ? ids[idx - 1] : ids[idx + 1];
        if (targetId === undefined) return;

        /* Disable both buttons while loading */
        var prevBtn = document.getElementById('ja-prev-btn');
        var nextBtn = document.getElementById('ja-next-btn');
        if (prevBtn) prevBtn.disabled = true;
        if (nextBtn) nextBtn.disabled = true;

        /* Optional: dim the panel while loading */
        var wrap = document.querySelector('.ja-two-col-wrap');
        if (wrap) wrap.classList.add('ja-nav-loading');

        var url = showUrlTpl.replace(':id', targetId);

        $.easyAjax({
            type: 'GET',
            url: url,
            success: function (res) {
                if (res.status === 'success') {
                    /* Replace sidebar content – the new panel's own script
                       will call updateNavUI() with its own CURRENT_ID */
                    $('#right-sidebar-content').html(res.view);

                    /* Scroll the target row into view in the list table */
                    var $row = $('[data-id="' + targetId + '"]');
                    if ($row.length) {
                        $row[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        /* Highlight briefly */
                        $row.addClass('table-active');
                        setTimeout(function () { $row.removeClass('table-active'); }, 1200);
                    }
                } else {
                    /* Re-enable on failure */
                    if (wrap) wrap.classList.remove('ja-nav-loading');
                    updateNavUI();
                }
            },
            error: function () {
                if (wrap) wrap.classList.remove('ja-nav-loading');
                updateNavUI();
            }
        });
    };

    /* ── Keyboard shortcut: ← → arrows ── */
    function onKeyDown(e) {
        /* Only fire when the sidebar is open and focus is not in a text input */
        var tag = document.activeElement ? document.activeElement.tagName : '';
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;

        if (e.key === 'ArrowLeft'  || e.keyCode === 37) {
            var prevBtn = document.getElementById('ja-prev-btn');
            if (prevBtn && !prevBtn.disabled) jaNavigate('prev', CURRENT_ID);
        }
        if (e.key === 'ArrowRight' || e.keyCode === 39) {
            var nextBtn = document.getElementById('ja-next-btn');
            if (nextBtn && !nextBtn.disabled) jaNavigate('next', CURRENT_ID);
        }
    }

    /* Remove any previously bound listener to avoid duplicates across reloads */
    document.removeEventListener('keydown', window._jaKeyNav);
    window._jaKeyNav = onKeyDown;
    document.addEventListener('keydown', window._jaKeyNav);

    /* ── Init ── */
    updateNavUI();

})();
/* ── Client Notes ── */
$('#add-client-note').click(function () {
    var jobId = $(this).data('job-id');
    $.easyAjax({
        type: 'POST',
        url: "{{ route('admin.job-client-notes.store') }}",
        data: {
            '_token': '{{ csrf_token() }}',
            'job_id': jobId,
            'note_text': $('#client_note_text').val()
        },
        success: function (response) {
            if (response.status === 'success') {
                $('#client-notes-list').html(response.view);
                $('#client_note_text').val('');
            }
        }
    });
});

$('body').on('click', '.edit-client-note', function () {
    $(this).hide();
    var noteId = $(this).data('note-id');
    var $noteEl = $('#cn-note-' + noteId);
    $noteEl.find('.cn-note-text').hide();
    var noteText = $noteEl.find('.cn-note-text').text().trim();
    var html = '<textarea id="cn-edit-text-' + noteId + '" class="ja-note-textarea" rows="3">' + noteText + '</textarea>'
        + '<button class="update-client-note ja-save-note-btn" data-note-id="' + noteId + '" style="margin-top:6px;background:#059669">'
        + '<i class="fa fa-check"></i> Save</button>';
    $noteEl.find('.cn-note-textarea').html(html);
});

$('body').on('click', '.update-client-note', function () {
    var noteId = $(this).data('note-id');
    var url = "{{ route('admin.job-client-notes.update', ':id') }}".replace(':id', noteId);
    $.easyAjax({
        type: 'POST', url: url,
        data: { '_token': '{{ csrf_token() }}', 'note': $('#cn-edit-text-' + noteId).val(), '_method': 'PUT' },
        success: function (response) {
            if (response.status === 'success') $('#client-notes-list').html(response.view);
        }
    });
});

$('body').on('click', '.delete-client-note', function () {
    var noteId = $(this).data('note-id');
    swal({
        title: "@lang('errors.areYouSure')", text: "@lang('errors.deleteWarning')", type: "warning",
        showCancelButton: true, confirmButtonColor: "#DD6B55", confirmButtonText: "@lang('app.delete')",
        cancelButtonText: "@lang('app.cancel')", closeOnConfirm: true, closeOnCancel: true
    }, function (isConfirm) {
        if (isConfirm) {
            var url = "{{ route('admin.job-client-notes.destroy', ':id') }}".replace(':id', noteId);
            $.easyAjax({
                type: 'POST', url: url,
                data: { '_token': '{{ csrf_token() }}', '_method': 'DELETE' },
                success: function (response) {
                    if (response.status === 'success') $('#client-notes-list').html(response.view);
                }
            });
        }
    });
});
</script>

@if(!is_null($application->skype_id))
<script src="https://swc.cdn.skype.com/sdk/v1/sdk.min.js"></script>
<div style="padding:12px 16px">
    <span class="skype-button rounded" data-contact-id="live:{{ $application->skype_id }}" data-text="Call"></span>
</div>
@endif