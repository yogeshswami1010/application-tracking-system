<div class="flex items-start gap-3 px-4 py-3 hover:bg-[#F8F7F4] transition cursor-pointer border-b border-[#F0EEE9]">
    <div style="width:32px;height:32px;border-radius:50%;background:#EFF6FF;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fa fa-at" style="color:#2563EB;font-size:13px;"></i>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:12.5px;font-weight:600;color:#1A1E2E;margin-bottom:2px;">
            {{ $notification->data['mentioned_by'] ?? 'Someone' }} mentioned you
        </div>
        <div style="font-size:12px;color:#5A6478;line-height:1.5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">
            "{{ $notification->data['note_text'] ?? '' }}"
        </div>
        <div style="font-size:10.5px;color:#B0B8C4;margin-top:3px;">
            {{ $notification->created_at->diffForHumans() }}
        </div>
    </div>
    @if(is_null($notification->read_at))
    <span style="width:7px;height:7px;border-radius:50%;background:#2563EB;flex-shrink:0;margin-top:4px;"></span>
    @endif
</div>