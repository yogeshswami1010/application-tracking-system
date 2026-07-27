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

        @if($user->cans('edit_job_applications') && $application->phone)
            <div style="display:flex;align-items:flex-end;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid #E8EAF0;">
                <textarea id="ja-shared-sms-message-{{ $application->id }}" rows="1" maxlength="1600"
                          placeholder="Type a message..."
                          onkeydown="if(event.key === 'Enter' && !event.shiftKey){event.preventDefault();jaSharedSendSms({{ $application->id }});}"
                          style="flex:1;min-height:40px;max-height:110px;resize:none;border:1.5px solid #D9E1EC;border-radius:11px;padding:10px 12px;font-family:inherit;font-size:12.5px;line-height:1.45;color:#1A1E2E;outline:none;"></textarea>
                <button type="button" id="ja-shared-sms-send-{{ $application->id }}" onclick="jaSharedSendSms({{ $application->id }})"
                        title="Send SMS" aria-label="Send SMS"
                        style="display:inline-flex;width:40px;height:40px;flex-shrink:0;align-items:center;justify-content:center;border:0;border-radius:11px;background:#2563EB;color:#fff;cursor:pointer;box-shadow:0 3px 8px rgba(37,99,235,.25);">
                    <i class="fa fa-paper-plane" style="font-size:13px"></i>
                </button>
            </div>
        @elseif(!$application->phone)
            <p style="margin-top:12px;padding:10px;border-radius:9px;background:#FFF7ED;color:#9A5B13;font-size:11.5px;text-align:center;">Add a phone number to send SMS messages.</p>
        @endif
        <p style="margin:9px 2px 0;font-size:10.5px;color:#A0A8B5;">Incoming replies appear here through the configured Telnyx webhook.</p>
    </div>
</div>

<script>
window.jaSharedSendSms = function (appId) {
    var field = document.getElementById('ja-shared-sms-message-' + appId);
    var button = document.getElementById('ja-shared-sms-send-' + appId);
    var message = field ? field.value.trim() : '';
    if (!message) {
        if (typeof toastr !== 'undefined') toastr.error('Please enter an SMS message.');
        return;
    }

    button.disabled = true;
    button.style.opacity = '.6';
    $.ajax({
        type: 'POST',
        url: "{{ route('admin.job-applications.send-sms', ':id') }}".replace(':id', appId),
        data: {_token: @json(csrf_token()), message: message},
        success: function (response) {
            if (response.status !== 'success') {
                if (typeof toastr !== 'undefined') toastr.error(response.message || 'SMS could not be sent.');
                return;
            }

            field.value = '';
            jaSharedAppendSms(appId, response.sms || {
                message: message,
                sender: 'ATS',
                time: ''
            });
            if (typeof toastr !== 'undefined') toastr.success(response.message || 'SMS sent successfully.');
        },
        error: function (xhr) {
            var response = xhr.responseJSON || {};
            var validation = response.errors && response.errors.message ? response.errors.message[0] : null;
            if (typeof toastr !== 'undefined') toastr.error(validation || response.message || 'SMS could not be sent.');
        },
        complete: function () {
            button.disabled = false;
            button.style.opacity = '1';
        }
    });
};

window.jaSharedAppendSms = function (appId, sms) {
    var history = document.getElementById('ja-shared-sms-history-' + appId);
    if (!history) return;
    var empty = document.getElementById('ja-shared-sms-empty-' + appId);
    if (empty) empty.remove();

    var row = document.createElement('div');
    row.style.cssText = 'display:flex;justify-content:flex-end';
    var bubble = document.createElement('div');
    bubble.style.cssText = 'max-width:86%;border-radius:12px;border-bottom-right-radius:4px;padding:9px 11px;background:#2563EB;color:#fff';
    var body = document.createElement('div');
    body.style.cssText = 'font-size:12px;line-height:1.5;white-space:pre-wrap;overflow-wrap:anywhere';
    body.textContent = sms.message || '';
    var meta = document.createElement('div');
    meta.style.cssText = 'margin-top:5px;font-size:9.5px;opacity:.7;text-align:right';
    meta.textContent = (sms.sender || 'ATS') + ' \u2022 ' + (sms.time || '');
    bubble.appendChild(body);
    bubble.appendChild(meta);
    row.appendChild(bubble);
    history.appendChild(row);
    history.scrollTop = history.scrollHeight;

    var count = document.getElementById('ja-shared-sms-count-' + appId);
    if (count) {
        var total = history.children.length;
        count.textContent = total + (total === 1 ? ' message' : ' messages');
    }
};
</script>
