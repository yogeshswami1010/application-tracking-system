@if(isset($candidateCalls) && $candidateCalls->isNotEmpty())
<div class="ja-card" style="margin-bottom:10px">
    <div class="ja-card-title"><i class="fa fa-phone"></i> Candidate calls</div>
    @foreach($candidateCalls as $call)
    <div style="padding:10px 0;border-bottom:1px solid #F0EEE9;font-size:12px;color:#1A1E2E">
        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
            <strong><i class="fa fa-phone" style="color:#2563EB"></i> {{ $call->phone }}</strong>
            <span style="color:#5A6478">{{ ucfirst(str_replace('_', ' ', $call->status)) }} · {{ gmdate('i:s', $call->duration_seconds) }}</span>
        </div>
        <div style="font-size:11px;color:#8892A0;margin-top:4px">
            Called by <strong style="color:#5A6478">{{ $call->user?->name ?? 'Unknown team member' }}</strong>
            · {{ $call->created_at->timezone('America/Toronto')->format('M j, Y g:i A') }} ET
        </div>
        @if($call->summary)
            <div style="margin-top:8px;white-space:pre-wrap;line-height:1.55"><strong>AI summary:</strong> {{ $call->summary }}</div>
        @elseif($call->status === 'not_recorded')
            <div style="margin-top:7px;color:#8892A0">Call ended without a recording.</div>
        @elseif(in_array($call->status, ['pending', 'failed', 'processing']))
            <div style="margin-top:7px;color:#8892A0">{{ $call->status === 'failed' ? 'Summary generation failed.' : 'Summary is '.($call->status === 'processing' ? 'processing.' : 'pending.') }}</div>
        @endif
        @if($call->transcript)
            <details style="margin-top:7px"><summary style="cursor:pointer;color:#2563EB">View transcript</summary><div style="margin-top:5px;white-space:pre-wrap">{{ $call->transcript }}</div></details>
        @endif
        @if($canCall && $call->user_id === $user->id && in_array($call->status, ['pending', 'failed', 'processing']))
            <button type="button" class="call-process ja-note-btn" style="margin-top:8px" data-url="{{ route('admin.candidate-calls.process', [$application->id, $call->id]) }}">Generate / retry summary</button>
        @endif
    </div>
    @endforeach
</div>
@endif
