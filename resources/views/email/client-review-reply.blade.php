<!doctype html><html lang="en"><body style="font-family:Arial,sans-serif;color:#17253E;padding:24px">
<h2 style="margin-top:0">New client review for {{ $review->candidate_name }}</h2>
<p><strong>Client:</strong> {{ $review->client_email }}</p>
<p><strong>Subject:</strong> {{ $review->subject }}</p>
<div style="white-space:pre-wrap;line-height:1.6">{{ $message->body_text }}</div>
<p style="margin-top:24px"><a href="{{ $atsUrl }}" style="display:inline-block;background:#2563EB;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none">Open conversation in ATS</a></p>
</body></html>
