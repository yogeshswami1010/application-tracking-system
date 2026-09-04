{{-- Right detail panel — matches job-applications-new.html detail-panel --}}
<style>
    /* Applicant profiles use the entire viewport. Other sidebar content keeps
       the standard 75vw width declared on the aside below. */
    #right-sidebar:has(> #right-sidebar-content .ja-two-col-wrap) {
        width: 100vw !important;
        max-width: 100vw !important;
        border-left: 0 !important;
    }
</style>
<div class="fixed inset-0 z-[190] hidden bg-[rgba(15,31,61,0.5)] backdrop-blur-[3px] transition-opacity duration-300" id="right-sidebar-backdrop" aria-hidden="true"></div>

<aside
    class="fixed top-0 right-0 z-[200] h-full w-full max-w-[75vw] translate-x-full border-l border-[#E8E6E1] bg-white shadow-[-8px_0_32px_rgba(15,31,61,0.08)] transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
    id="right-sidebar"
    role="complementary"
    aria-label="@lang('menu.jobApplications')">
    <div class="h-full overflow-y-auto overscroll-contain [scrollbar-width:thin]" id="right-sidebar-content">
    </div>
</aside>
