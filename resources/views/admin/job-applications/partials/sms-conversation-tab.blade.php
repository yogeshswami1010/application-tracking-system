<div id="ja-tab-sms" class="ja-tab-pane" style="display:none">
    <div class="ja-card">
        <div class="ja-card-title" style="justify-content:space-between;">
            <span><i class="fa fa-comments-o" style="font-size:11px"></i> SMS Conversation</span>
            <span id="ja-shared-sms-count-{{ $application->id }}" style="font-size:10.5px;color:#8892A0;font-weight:600;">{{ $application->smsMessages->count() }} messages</span>
        </div>

        <div id="ja-shared-sms-history-{{ $application->id }}" style="display:flex;flex-direction:column;gap:9px;max-height:calc(100vh - 245px);overflow-y:auto;padding:4px 2px;">
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
                <div id="ja-shared-sms-empty-{{ $application->id }}" style="padding:30px 8px;text-align:center;color:#A0A8B5;font-size:12px;">
                    <i class="fa fa-comment-o" style="display:block;font-size:24px;margin-bottom:8px;"></i>
                    No SMS messages yet.
                </div>
            @endforelse
        </div>

        @if(!$application->phone)
            <p style="margin-top:12px;padding:10px;border-radius:9px;background:#FFF7ED;color:#9A5B13;font-size:11.5px;text-align:center;">Add a phone number to send SMS messages.</p>
        @endif
        <p style="margin:9px 2px 0;font-size:10.5px;color:#A0A8B5;">Incoming replies appear here through the configured Telnyx webhook.</p>
    </div>
</div>
