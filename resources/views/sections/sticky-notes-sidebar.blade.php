{{-- Sticky notes: right slide-over panel (separate from #right-sidebar used by job apps) --}}
<div class="fixed inset-0 z-[185] hidden bg-[rgba(15,31,61,0.45)] backdrop-blur-[2px] transition-opacity duration-300" id="sticky-notes-backdrop" aria-hidden="true"></div>

{{-- Desktop / tablet: compact tab on right edge --}}
<button type="button"
        id="sticky-notes-trigger"
        class="ra-sticky-notes-trigger fixed z-[210] hidden md:flex flex-col items-center justify-center gap-2 rounded-l-xl border border-r-0 border-[#E8E6E1] bg-[#0F1F3D] px-2.5 py-3 text-white shadow-[-6px_0_24px_rgba(15,31,61,0.18)] transition hover:bg-[#162849] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2 focus:ring-offset-[#F8F7F4]"
        style="top: 50%; transform: translateY(-50%); right: 0;"
        title="@lang('app.menu.stickyNotes')"
        aria-expanded="false"
        aria-controls="sticky-notes-sidebar"
        aria-label="@lang('app.menu.stickyNotes')">
    <svg class="h-5 w-5 shrink-0 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <span id="sticky-note-count-badge" class="inline-flex min-h-[1.35rem] min-w-[1.35rem] items-center justify-center rounded-full bg-[#FEF3C7] px-1.5 text-[11px] font-bold tabular-nums text-[#92400E]">{{ $stickyNotes->count() }}</span>
</button>

{{-- Mobile: FAB --}}
<div class="fixed bottom-5 right-4 z-[210] md:hidden" id="sticky-notes-trigger-mobile-wrap">
    <button type="button"
            id="sticky-notes-trigger-mobile"
            class="relative flex h-14 w-14 items-center justify-center rounded-2xl border border-[#E8E6E1] bg-[#0F1F3D] text-white shadow-lg transition hover:bg-[#162849] focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2"
            title="@lang('app.menu.stickyNotes')"
            aria-expanded="false"
            aria-controls="sticky-notes-sidebar"
            aria-label="@lang('app.menu.stickyNotes')">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span class="absolute -right-1 -top-1 flex h-6 min-w-[1.5rem] items-center justify-center rounded-full border-2 border-white bg-[#FEF3C7] px-1 text-[11px] font-bold text-[#92400E]" id="sticky-note-count-badge-mobile">{{ $stickyNotes->count() }}</span>
    </button>
</div>

<aside id="sticky-notes-sidebar"
       class="fixed top-0 right-0 z-[195] flex h-full w-full max-w-[400px] translate-x-full flex-col border-l border-[#E8E6E1] bg-[#F8F7F4] shadow-[-12px_0_48px_rgba(15,31,61,0.12)] transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
       role="complementary"
       aria-label="@lang('app.menu.stickyNotes')"
       aria-hidden="true">
    <div class="shrink-0 border-b border-[#E8E6E1] bg-white px-5 py-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-[16px] font-bold tracking-tight text-[#1A1E2E]">@lang('app.menu.stickyNotes')</h2>
                <p class="mt-0.5 text-[12px] leading-snug text-[#8892A0]">{{ __('modules.sticky.sidebarHint') }}</p>
            </div>
            <button type="button"
                    id="sticky-notes-close"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[#8892A0] transition hover:bg-[#F1F3F7] focus:outline-none focus:ring-2 focus:ring-slate-300"
                    aria-label="@lang('app.close')">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#EEF0F5] px-3 py-1 text-[12px] font-semibold text-[#5A6478]">
                <span class="text-[#8892A0]">@lang('app.total')</span>
                <span id="sticky-note-count" class="tabular-nums text-[#1A1E2E]">{{ $stickyNotes->count() }}</span>
            </span>
            <button type="button"
                    onclick="showCreateNoteModal();"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-3.5 py-2 text-[12.5px] font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                @lang('modules.sticky.addNote')
            </button>
        </div>
    </div>
    <div id="sticky-note-list" class="sticky-notes-panel min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-4 [scrollbar-width:thin]">
        @include('admin.sticky-note.note-ajax', ['stickyNotes' => $stickyNotes])
    </div>
</aside>
