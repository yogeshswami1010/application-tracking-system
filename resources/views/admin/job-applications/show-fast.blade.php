@php
    $statusColor = $application->status?->color ?? '#6B7280';
    $initials = collect(explode(' ', $application->full_name ?: '?'))->map(fn ($word) => strtoupper(substr($word, 0, 1)))->take(2)->join('');
@endphp

<div style="height:100%;min-height:100%;background:#f8fafc;font-family:'Plus Jakarta Sans',sans-serif;overflow:auto;">
    <div style="display:flex;align-items:center;gap:12px;padding:16px 20px;background:#0f1f3d;color:#fff;">
        <div style="width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;font-weight:700;flex:0 0 auto;">{{ $initials }}</div>
        <div style="min-width:0;flex:1;">
            <div style="font-size:17px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ ucwords($application->full_name) }}</div>
            <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:3px;">{{ ucwords($application->job?->title ?? 'Applicant') }}</div>
        </div>
        <span style="background:{{ $statusColor }};border-radius:16px;padding:5px 10px;font-size:11px;font-weight:600;">{{ ucwords(str_replace('_', ' ', $application->status?->status ?? 'New')) }}</span>
        <button type="button" class="right-side-toggle" style="border:0;background:transparent;color:#fff;font-size:22px;cursor:pointer;line-height:1;">&times;</button>
    </div>

    <div style="max-width:760px;margin:0 auto;padding:20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:14px;">
        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
            <h3 style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin:0 0 13px;">Personal information</h3>
            <div style="font-size:14px;color:#1e293b;line-height:2;word-break:break-word;">
                <div><i class="fa fa-envelope-o" style="width:18px;color:#64748b;"></i> <a href="mailto:{{ $application->email }}" style="color:#2563eb;">{{ $application->email ?: '—' }}</a></div>
                <div><i class="fa fa-phone" style="width:18px;color:#64748b;"></i> {{ $application->phone ?: '—' }}</div>
                <div><i class="fa fa-map-marker" style="width:18px;color:#64748b;"></i> {{ $application->location?->location ?: ($application->city ?: '—') }}</div>
            </div>
        </section>

        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
            <h3 style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin:0 0 13px;">Application</h3>
            <div style="font-size:14px;color:#1e293b;line-height:2;">
                <div><strong>Job:</strong> {{ ucwords($application->job?->title ?? '—') }}</div>
                <div><strong>Company:</strong> {{ ucwords($application->job?->company?->company_name ?? '—') }}</div>
                <div><strong>Applied:</strong> {{ optional($application->created_at)->format('d M Y') }}</div>
            </div>
        </section>

        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;grid-column:1/-1;">
            <h3 style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin:0 0 13px;">CV and complete profile</h3>
            <div style="display:flex;flex-wrap:wrap;gap:9px;">
                @if($resumeUrl)
                    <a href="{{ $resumeUrl }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm"><i class="fa fa-file-pdf-o"></i> Open CV</a>
                    <a href="{{ $resumeUrl }}" download class="btn btn-outline-secondary btn-sm"><i class="fa fa-download"></i> Download CV</a>
                @else
                    <span style="font-size:13px;color:#64748b;align-self:center;">No CV uploaded.</span>
                @endif
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="jaOpenFullProfile({{ $application->id }})"><i class="fa fa-expand"></i> Open full profile</button>
            </div>
        </section>
    </div>
</div>

<script>
function jaOpenFullProfile(id) {
    var url = "{{ route('admin.job-applications.show', ':id') }}".replace(':id', id) + '?full=1';
    $.ajax({ type: 'GET', url: url, success: function (response) {
        if (response.status === 'success') $('#right-sidebar-content').html(response.view);
    }});
}
</script>
