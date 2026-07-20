@if($statusHistories->isNotEmpty())
<div class="ja-card" style="margin-bottom:10px">
    <div class="ja-card-title"><i class="fa fa-exchange"></i> Stage Activity</div>
    @foreach($statusHistories as $hist)
    <div style="padding:10px 0;border-bottom:1px solid #F0EEE9;font-size:12px;color:#1A1E2E">
        <div>
            <strong>{{ $hist->user?->name ?? 'Auto (screening rule)' }}</strong>
            — {{ $hist->notes ?: 'moved this applicant' }}
        </div>
        @if($hist->fromStatus || $hist->toStatus)
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:7px">
            @if($hist->fromStatus)
            <span style="display:inline-flex;padding:3px 8px;border-radius:20px;font-size:10.5px;font-weight:600;color:#fff;background:{{ $hist->fromStatus->color ?? '#6B7280' }}">
                {{ ucwords(str_replace('_', ' ', $hist->fromStatus->status)) }}
            </span>
            @endif
            @if($hist->fromStatus && $hist->toStatus)
            <i class="fa fa-long-arrow-right" style="color:#8A94A6"></i>
            @endif
            @if($hist->toStatus)
            <span style="display:inline-flex;padding:3px 8px;border-radius:20px;font-size:10.5px;font-weight:600;color:#fff;background:{{ $hist->toStatus->color ?? '#6B7280' }}">
                {{ ucwords(str_replace('_', ' ', $hist->toStatus->status)) }}
            </span>
            @endif
        </div>
        @endif
        <div style="font-size:11px;color:#B0B8C4;margin-top:5px">{{ $hist->created_at->timezone('America/Toronto')->format('d M Y, h:i A') }}</div>
    </div>
    @endforeach
</div>
@endif

@forelse($previousApps as $prev)
<div class="ja-card" style="margin-bottom:10px">
    <div style="font-size:13.5px;font-weight:700;color:#1A1E2E">{{ ucwords($prev->job?->title ?? '—') }}</div>
    <div style="font-size:11px;color:#8A94A6;margin-top:4px">{{ $prev->created_at?->format('d M Y') }} @if($prev->location) · {{ ucwords($prev->location->location) }} @endif</div>
    <span style="display:inline-flex;margin-top:8px;padding:3px 10px;border-radius:20px;font-size:11px;color:#fff;background:{{ $prev->status?->color ?? '#6B7280' }}">{{ ucwords(str_replace('_',' ', $prev->status?->status ?? '—')) }}</span>
</div>
@empty
@if($statusHistories->isEmpty())
<div style="text-align:center;padding:24px;color:#B0B8C4;font-size:12.5px">No history available.</div>
@endif
@endforelse
