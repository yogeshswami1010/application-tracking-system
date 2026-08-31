@if(isset($resumeHistories) && $resumeHistories->isNotEmpty())
<div id="ja-resume-history-card" class="ja-card" style="margin-bottom:10px">
    <div class="ja-card-title"><i class="fa fa-file-pdf-o"></i> Previous CVs</div>
    @foreach($resumeHistories as $resumeHistory)
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #F0EEE9">
            <div style="min-width:0">
                <div style="font-size:12.5px;font-weight:700;color:#1A1E2E;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ $resumeHistory->original_name ?: 'Previous resume' }}
                </div>
                <div style="font-size:11px;color:#8892A0;margin-top:3px">
                    Updated by <strong>{{ $resumeHistory->updatedBy?->name ?? 'Unknown team member' }}</strong>
                    · {{ $resumeHistory->created_at->timezone('America/Toronto')->format('d M Y, h:i A') }}
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0">
                <a href="{{ $resumeHistory->resume_url }}" target="_blank" rel="noopener" class="ja-note-btn"><i class="fa fa-eye"></i> View</a>
                <a href="{{ $resumeHistory->resume_url }}" download class="ja-note-btn"><i class="fa fa-download"></i></a>
            </div>
        </div>
    @endforeach
</div>
@endif
