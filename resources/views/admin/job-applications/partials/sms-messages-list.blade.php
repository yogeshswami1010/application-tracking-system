@forelse($application->smsMessages as $smsMessage)
    <div style="display:flex;justify-content:{{ $smsMessage->direction === 'outbound' ? 'flex-end' : 'flex-start' }};">
        <div style="max-width:86%;border-radius:12px;padding:9px 11px;{{ $smsMessage->direction === 'outbound' ? 'background:#2563EB;color:#fff;border-bottom-right-radius:4px;' : 'background:#F1F3F7;color:#1A1E2E;border-bottom-left-radius:4px;' }}">
            <div style="font-size:12px;line-height:1.5;white-space:pre-wrap;overflow-wrap:anywhere;">{{ $smsMessage->message }}</div>
            <div style="margin-top:5px;font-size:9.5px;opacity:.7;text-align:{{ $smsMessage->direction === 'outbound' ? 'right' : 'left' }};">
                {{ $smsMessage->direction === 'outbound' ? ($smsMessage->user?->name ?? 'ATS') : $application->full_name }}
                &bull; {{ ($smsMessage->received_at ?? $smsMessage->created_at)?->copy()->timezone('America/Toronto')->format('M j, Y g:i A') }} ET
            </div>
        </div>
    </div>
@empty
    <div style="padding:30px 8px;text-align:center;color:#A0A8B5;font-size:12px;">
        <i class="fa fa-comment-o" style="display:block;font-size:24px;margin-bottom:8px;"></i>
        No SMS messages yet.
    </div>
@endforelse