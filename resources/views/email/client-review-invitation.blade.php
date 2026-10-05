<!doctype html>
<html lang="en"><body style="margin:0;padding:24px;background:#F3F5F9;font-family:Arial,sans-serif;color:#17253E">
<table role="presentation" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center">
<table role="presentation" cellpadding="24" cellspacing="0" width="600" style="width:100%;max-width:600px;background:#fff;border-radius:12px"><tr><td>
    <p style="font-size:12px;color:#64748B;margin:0 0 8px">CANDIDATE REVIEW</p>
    <h1 style="font-size:22px;margin:0 0 6px">{{ $review->candidate_name }}</h1>
    @if($review->job_title)<p style="color:#64748B;margin:0 0 20px">{{ $review->job_title }}</p>@endif
    <div style="font-size:15px;line-height:1.6">{!! $bodyHtml !!}</div>
    <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:24px"><tr><td bgcolor="#2563EB" style="border-radius:8px"><a href="{{ $reviewUrl }}" style="display:inline-block;padding:14px 22px;color:#fff;text-decoration:none;font-size:15px;font-weight:bold">View candidate &amp; give review</a></td></tr></table>
    <p style="font-size:12px;color:#64748B;margin:16px 0 0">This private link expires {{ $review->expires_at->format('d M Y') }}. Use the reply form on the candidate page to send your review.</p>
    {!! $signatureHtml !!}
</td></tr></table>
</td></tr></table>
</body></html>
