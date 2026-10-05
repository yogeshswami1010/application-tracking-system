@forelse($reviews as $review)
<div class="ja-card ja-review-thread">
    <div style="font-size:12px;font-weight:700;overflow-wrap:anywhere">{{ $review->client_email }}</div>
    <div style="font-size:11px;color:#64748B;margin:4px 0 9px">{{ $review->subject }}</div>
    <div class="ja-review-meta">
        @if($review->revoked_at)Access revoked
        @elseif($review->expires_at->isPast())Link expired
        @elseif(!$review->sent_at)Email not sent yet
        @else Link active until {{ $review->expires_at->format('d M Y') }}
        @endif
    </div>
    @foreach($review->messages->sortBy('id') as $item)
    <div class="ja-review-message {{ $item->direction }}">
        <div style="font-size:11px;font-weight:700;margin-bottom:7px">{{ $item->direction === 'outbound' ? ($item->user?->name ?? 'ATS team') : $review->client_email }}</div>
        @if($item->direction === 'outbound')<div class="ja-review-html">{!! \App\Services\ClientReviewContent::clean($item->body_html ?? '') !!}</div>
        @else<div style="font-size:12px;line-height:1.6;white-space:pre-wrap;overflow-wrap:anywhere">{{ $item->body_text }}</div>@endif
        <div class="ja-review-meta">{{ $item->created_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}
            @if($item->direction === 'outbound') · {{ $item->mail_status === 'sent' ? 'Email sent' : ($item->mail_status === 'failed' ? 'Email failed' : 'Pending') }}
            @elseif(!$item->notification_sent_at && !$item->notification_skipped_at) · Saved in ATS; email notification pending
            @endif
        </div>
        @if($canEdit && $item->direction === 'outbound' && $item->mail_status !== 'sent' && !$review->revoked_at && $review->expires_at->isFuture())
        <button type="button" class="ja-pdf-btn" style="margin-top:8px" data-client-review-retry="{{ $review->id }}" data-message="{{ $item->id }}">Retry email</button>
        @endif
    </div>
    @endforeach
    @if($canEdit && $review->sent_at && !$review->revoked_at && $review->expires_at->isFuture())
    <details class="ja-review-reply"><summary>Reply to client</summary>
        <form data-client-review-reply="{{ $review->id }}">
            @include('admin.job-applications.partials.client-review-editor', ['editorId' => 'ja-review-reply-'.$review->id])
            <div class="ja-review-actions"><button type="submit" class="ja-pdf-btn ja-pdf-btn-primary">Send reply</button></div>
            <div class="ja-review-feedback" role="status" aria-live="polite"></div>
        </form>
    </details>
    @endif
    @if($canEdit && !$review->revoked_at && $review->expires_at->isFuture())
    <div class="ja-review-actions"><button type="button" class="ja-pdf-btn" data-client-review-revoke="{{ $review->id }}">Revoke client access</button></div>
    @endif
    <div class="ja-review-thread-feedback ja-review-feedback" role="status" aria-live="polite"></div>
</div>
@empty
<div class="ja-card" style="text-align:center;color:#94A3B8;font-size:12px;padding:26px">No client review conversations yet.</div>
@endforelse
