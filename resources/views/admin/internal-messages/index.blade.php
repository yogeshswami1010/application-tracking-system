@extends('layouts.app')

@section('page-title-html')
    Internal <em>Messages</em>
@endsection

@section('page-subtitle')
    Private conversations with your ATS team members
@endsection

@push('head-script')
<style>
.im-conversation-layout { display:grid; grid-template-columns:300px minmax(0,1fr); }
.im-contact-panel { min-width:0; }
.im-chat-panel { min-width:0; }
@media (max-width:767px) {
    .im-conversation-layout { display:flex; flex-direction:column; height:auto !important; min-height:0 !important; }
    .im-contact-panel { max-height:280px; border-right:0 !important; }
    .im-chat-panel { min-height:560px; }
}
</style>
@endpush
@section('content')
<div class="overflow-hidden rounded-2xl border border-[#E8E6E1] bg-white shadow-sm" style="height:calc(100vh - 170px);min-height:560px;">
    <div class="im-conversation-layout h-full">
        <aside class="im-contact-panel flex min-h-0 flex-col border-b border-r border-[#E8E6E1]">
            <div class="border-b border-[#F0EEE9] p-4">
                <h2 class="text-[15px] font-bold text-[#1A1E2E]">Team Members</h2>
                <p class="mt-1 text-[11.5px] text-[#8892A0]">Select someone to start messaging</p>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto p-2">
                @forelse($teamMembers as $member)
                    @php
                        $activeMember = $selectedMember && (int) $selectedMember->id === (int) $member->id;
                        $unread = (int) ($unreadCounts[$member->id] ?? 0);
                        $online = $member->last_seen_at && \Carbon\Carbon::parse($member->last_seen_at)->gte(now()->subSeconds(75));
                    @endphp
                    <a href="{{ route('admin.internal-messages.index', ['recipient' => $member->id]) }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ $activeMember ? 'bg-[#EFF6FF]' : 'hover:bg-[#F8F9FB]' }}">
                        <span class="relative inline-flex shrink-0">
                            <img src="{{ $member->profile_image_url }}" alt="" class="h-10 w-10 rounded-full object-cover ring-2 ring-[#EEF0F5]">
                            <span data-ats-presence-user="{{ $member->id }}" class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white {{ $online ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[13px] font-semibold text-[#1A1E2E]">{{ ucwords($member->name) }}</span>
                            <span class="block truncate text-[11px] text-[#8892A0]">{{ ucwords($member->role?->role?->display_name ?? 'Team Member') }}</span>
                        </span>
                        <span data-unread-member="{{ $member->id }}" class="{{ $unread > 0 ? '' : 'hidden' }} min-w-[21px] rounded-full bg-[#2563EB] px-1.5 py-0.5 text-center text-[10px] font-bold text-white">{{ $unread }}</span>
                    </a>
                @empty
                    <div class="px-4 py-10 text-center text-[12px] text-[#8892A0]">No other team members are available.</div>
                @endforelse
            </div>
        </aside>

        <section class="im-chat-panel flex min-h-0 flex-col">
            @if($selectedMember)
                <header class="flex items-center gap-3 border-b border-[#F0EEE9] px-5 py-3.5">
                    <span class="relative inline-flex shrink-0">
                        <img src="{{ $selectedMember->profile_image_url }}" alt="" class="h-10 w-10 rounded-full object-cover ring-2 ring-[#EEF0F5]">
                        <span data-ats-presence-user="{{ $selectedMember->id }}" class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-red-500"></span>
                    </span>
                    <div class="min-w-0">
                        <h2 class="truncate text-[14px] font-bold text-[#1A1E2E]">{{ ucwords($selectedMember->name) }}</h2>
                        <p class="truncate text-[11.5px] text-[#8892A0]">{{ $selectedMember->email }}</p>
                    </div>
                </header>

                <div id="internal-message-list" class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-[#F8F9FB] px-4 py-5 sm:px-6">
                    @forelse($conversationMessages as $message)
                        @php($mine = (int) $message->sender_id === (int) $user->id)
                        <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}" data-internal-message-id="{{ $message->id }}">
                            <div class="max-w-[82%] rounded-2xl px-4 py-2.5 shadow-sm {{ $mine ? 'rounded-br-md bg-[#2563EB] text-white' : 'rounded-bl-md border border-[#E8E6E1] bg-white text-[#1A1E2E]' }}">
                                <p class="whitespace-pre-wrap break-words text-[13px] leading-relaxed">{{ $message->body }}</p>
                                <p class="mt-1 text-[10px] {{ $mine ? 'text-blue-100' : 'text-[#A0A8B5]' }}">{{ $message->created_at->timezone($global->timezone)->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    @empty
                        <div id="internal-message-empty" class="flex h-full flex-col items-center justify-center text-center">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#EFF6FF] text-[#2563EB]">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a10.6 10.6 0 01-4.38-.91L3 20l1.23-3.28A7.35 7.35 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </div>
                            <p class="text-[13px] font-semibold text-[#5A6478]">No messages yet</p>
                            <p class="mt-1 text-[11.5px] text-[#A0A8B5]">Send the first internal message below.</p>
                        </div>
                    @endforelse
                </div>

                <form id="internal-message-form" class="border-t border-[#E8E6E1] bg-white p-3 sm:p-4">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $selectedMember->id }}">
                    <div class="flex items-end gap-2">
                        <textarea name="body" id="internal-message-body" rows="1" maxlength="5000" required placeholder="Type a message..." class="max-h-32 min-h-[44px] flex-1 resize-none rounded-xl border border-[#DDE2EA] px-4 py-3 text-[13px] text-[#1A1E2E] outline-none transition focus:border-[#2563EB] focus:ring-2 focus:ring-blue-100"></textarea>
                        <button type="submit" id="internal-message-send" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#2563EB] text-white shadow-sm transition hover:bg-[#1D4ED8] disabled:cursor-not-allowed disabled:opacity-60" aria-label="Send message">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </button>
                    </div>
                    <p class="mt-2 text-[10.5px] text-[#A0A8B5]">The recipient will also receive this message by email.</p>
                </form>
            @else
                <div class="flex h-full items-center justify-center px-6 text-center">
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">No team member selected</h2>
                        <p class="mt-1 text-[12px] text-[#8892A0]">Add another team member before starting a conversation.</p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

@if($selectedMember)
@push('footer-script')
<script>
(function () {
    var recipientId = @json((int) $selectedMember->id);
    var conversationUrl = @json(route('admin.internal-messages.conversation', $selectedMember->id));
    var sendUrl = @json(route('admin.internal-messages.store'));
    var list = document.getElementById('internal-message-list');
    var sending = false;

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function renderMessages(messages) {
        if (!list) return;
        var wasNearBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 120;
        if (!messages.length) return;
        list.innerHTML = messages.map(function (message) {
            var align = message.mine ? 'justify-end' : 'justify-start';
            var bubble = message.mine
                ? 'rounded-br-md bg-[#2563EB] text-white'
                : 'rounded-bl-md border border-[#E8E6E1] bg-white text-[#1A1E2E]';
            var time = message.mine ? 'text-blue-100' : 'text-[#A0A8B5]';
            return '<div class="flex '+align+'" data-internal-message-id="'+message.id+'">'
                +'<div class="max-w-[82%] rounded-2xl px-4 py-2.5 shadow-sm '+bubble+'">'
                +'<p class="whitespace-pre-wrap break-words text-[13px] leading-relaxed">'+escapeHtml(message.body)+'</p>'
                +'<p class="mt-1 text-[10px] '+time+'">'+escapeHtml(message.time)+'</p>'
                +'</div></div>';
        }).join('');
        if (wasNearBottom || !list.dataset.loaded) {
            list.scrollTop = list.scrollHeight;
            list.dataset.loaded = '1';
        }
    }

    function updateUnread(counts) {
        $('[data-unread-member]').each(function () {
            var count = Number(counts[String($(this).data('unread-member'))] || 0);
            $(this).text(count).toggleClass('hidden', count < 1);
        });
    }

    function loadConversation() {
        if (document.hidden || sending) return;
        $.ajax({ url: conversationUrl, type: 'GET', cache: false, global: false })
            .done(function (response) {
                renderMessages(response.messages || []);
                updateUnread(response.unread_counts || {});
            });
    }

    $('#internal-message-form').on('submit', function (event) {
        event.preventDefault();
        var body = $.trim($('#internal-message-body').val());
        if (!body || sending) return;
        sending = true;
        $('#internal-message-send').prop('disabled', true);
        $.ajax({
            url: sendUrl,
            type: 'POST',
            data: { _token: @json(csrf_token()), recipient_id: recipientId, body: body }
        }).done(function () {
            $('#internal-message-body').val('').css('height', '');
            loadConversation();
        }).fail(function (xhr) {
            var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The message could not be sent.';
            if ($.toast) $.toast({ heading: 'Message not sent', text: message, icon: 'error' });
            else alert(message);
        }).always(function () {
            sending = false;
            $('#internal-message-send').prop('disabled', false);
            loadConversation();
        });
    });

    $('#internal-message-body').on('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 128) + 'px';
    }).on('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            $('#internal-message-form').trigger('submit');
        }
    });

    if (list) list.scrollTop = list.scrollHeight;
    setInterval(loadConversation, 5000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) loadConversation(); });
})();
</script>
@endpush
@endif