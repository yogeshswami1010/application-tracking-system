@php
    $applicationId = $notification->data['application_id'] ?? null;
    $profileUrl = $applicationId
        ? route('admin.job-applications.table', ['open' => $applicationId, 'tab' => 'sms'])
        : route('admin.job-applications.table');
@endphp

<a href="javascript:;"
   data-link="{{ $profileUrl }}"
   class="read-notification block"
   data-notification-id="{{ $notification->id }}">
    <div class="flex items-start gap-3 border-b border-[#F0EEE9] px-4 py-3 transition hover:bg-[#F8F7F4]">
        <div style="width:32px;height:32px;border-radius:50%;background:#ECFDF5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fa fa-commenting-o" style="color:#059669;font-size:13px;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:12.5px;font-weight:600;color:#1A1E2E;margin-bottom:2px;">
                {{ $notification->data['message'] ?? 'An applicant replied to your SMS.' }}
            </div>
            <div style="font-size:12px;color:#5A6478;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;max-width:220px;">
                “{{ $notification->data['sms_message'] ?? '' }}”
            </div>
            <div style="font-size:10.5px;color:#B0B8C4;margin-top:3px;">
                {{ $notification->created_at->diffForHumans() }}
            </div>
        </div>
        @if(is_null($notification->read_at))
            <span style="width:7px;height:7px;border-radius:50%;background:#059669;flex-shrink:0;margin-top:4px;"></span>
        @endif
    </div>
</a>
