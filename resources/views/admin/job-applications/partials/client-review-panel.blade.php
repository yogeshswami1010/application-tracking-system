<style>
    .ja-review-field{width:100%;padding:9px 11px;border:1px solid #E2E8F0;border-radius:8px;font:12px 'Plus Jakarta Sans',sans-serif;background:#fff;margin:5px 0 12px}
    .ja-review-label{font-size:12px;font-weight:600;color:#475569;display:block}.ja-review-editor-wrap{border:1px solid #CBD5E1;border-radius:9px;overflow:hidden;background:#fff}.ja-review-format{display:flex;align-items:center;gap:4px;flex-wrap:wrap;padding:6px;background:#F8FAFC;border-bottom:1px solid #E2E8F0}.ja-review-format button,.ja-review-format select{border:1px solid #E2E8F0;border-radius:4px;background:#fff;color:#475569;min-height:26px;padding:2px 6px;font:11px Arial,sans-serif;cursor:pointer}.ja-review-format button:hover{background:#EFF6FF}.ja-review-editor{min-height:150px;max-height:320px;overflow:auto;padding:12px;font:13px/1.6 Arial,sans-serif;color:#17253E;overflow-wrap:anywhere}.ja-review-editor:focus{outline:2px solid #93C5FD;outline-offset:-2px}.ja-review-editor:empty:before{content:attr(data-placeholder);color:#94A3B8;pointer-events:none}.ja-review-editor ul,.ja-review-editor ol,.ja-review-html ul,.ja-review-html ol{padding-left:22px}.ja-review-editor p,.ja-review-html p{margin:0 0 8px}.ja-review-html{font:12px/1.6 Arial,sans-serif;overflow-wrap:anywhere}.ja-review-message{padding:11px;border-radius:10px;margin:10px 0;background:#F1F5F9}.ja-review-message.outbound{background:#EFF6FF}.ja-review-meta{font-size:10px;color:#64748B;margin-top:6px}.ja-review-feedback{display:none;font-size:12px;margin-top:10px;overflow-wrap:anywhere}.ja-review-thread{margin-bottom:14px}.ja-review-thread summary{cursor:pointer;font-size:12px;font-weight:700;overflow-wrap:anywhere}.ja-review-actions{display:flex;align-items:center;flex-wrap:wrap;gap:6px;margin-top:10px}.ja-review-reply{margin-top:12px}.ja-review-reply>summary{margin-bottom:10px;font-size:12px;color:#2563EB;cursor:pointer}
    .ja-review-editor ul,.ja-review-html ul{list-style-type:disc}.ja-review-editor ol,.ja-review-html ol{list-style-type:decimal}
</style>
<div id="ja-client-reviews-panel" data-application-id="{{ $application->id }}">
    @if($user->cans('edit_job_applications'))
    <div class="ja-card">
        <div class="ja-card-title"><i class="fa fa-paper-plane-o"></i> Send profile to client</div>
        <form data-client-review-send>
            <label class="ja-review-label" for="ja-review-email">Client email</label>
            <input id="ja-review-email" class="ja-review-field" name="client_email" type="email" maxlength="255" required placeholder="client@company.com">
            <label class="ja-review-label" for="ja-review-subject">Subject</label>
            <input id="ja-review-subject" class="ja-review-field" name="subject" type="text" maxlength="191" required value="Candidate for review: {{ mb_substr($application->full_name, 0, 160) }}">
            <label class="ja-review-label" style="margin-bottom:7px" for="ja-review-compose">Message</label>
            @include('admin.job-applications.partials.client-review-editor', ['editorId' => 'ja-review-compose'])
            <div class="ja-review-actions"><button type="submit" class="ja-pdf-btn ja-pdf-btn-primary"><i class="fa fa-paper-plane-o"></i> Send to client</button></div>
            <p style="font-size:11px;color:#64748B;margin:10px 0 0">The email includes your message and a private CV review button. The link expires in 30 days.</p>
            <div class="ja-review-feedback" role="status" aria-live="polite"></div>
        </form>
    </div>
    @endif
    <div id="ja-client-review-conversations"><div class="ja-tab-loading">Open Client Reviews to load conversations.</div></div>
</div>
<script type="application/json" id="ja-client-review-config">{!! json_encode([
    'token' => csrf_token(), 'applicationId' => (int) $application->id,
    'indexUrl' => route('admin.client-reviews.index', $application->id),
    'unreadUrl' => route('admin.client-reviews.unread', $application->id),
    'sendUrl' => route('admin.client-reviews.send', $application->id),
    'replyUrl' => route('admin.client-reviews.reply', [$application->id, '__REVIEW__']),
    'revokeUrl' => route('admin.client-reviews.revoke', [$application->id, '__REVIEW__']),
    'retryUrl' => route('admin.client-reviews.retry', [$application->id, '__REVIEW__', '__MESSAGE__']),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
