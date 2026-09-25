@if(auth()->user()->cans('view_job_applications') && auth()->user()->cans('edit_job_applications'))
<div id="candidate-communications" data-preview-url="{{ route('admin.candidate-communications.preview') }}" data-send-url="{{ route('admin.candidate-communications.send') }}" data-templates-url="{{ route('admin.candidate-communications.templates') }}" data-source="{{ request()->routeIs('admin.ai-search') ? 'ai-search' : 'candidates' }}" data-token="{{ csrf_token() }}">
    <div class="cc-toolbar">
        @if(request()->routeIs('admin.job-applications.index'))<label><input type="checkbox" class="cc-all"> Select all loaded candidates</label>@endif
        <strong><span id="cc-count">0</span> candidates selected</strong>
        <button type="button" data-cc-open="email">Bulk email</button>
        <button type="button" data-cc-open="sms">Bulk SMS</button>
        <span>Select up to 100 candidates. Select all applies to the current page.</span>
    </div>
    <dialog id="cc-dialog" aria-labelledby="cc-title">
        <form id="cc-form">
            <h2 id="cc-title">Bulk email</h2>
            <p id="cc-summary" role="status"></p>
            <details><summary>Review recipients</summary><ul id="cc-recipients"></ul></details>
            <div id="cc-email-fields">
                <label for="cc-template">Saved email template</label>
                <select id="cc-template"><option value="">Choose a template</option></select>
                <label for="cc-subject">Subject</label><input id="cc-subject" maxlength="191">
                <label for="cc-template-name">Template name</label><input id="cc-template-name" maxlength="80" placeholder="e.g. Interview invitation">
                <button type="button" id="cc-save-template">Save email as a template</button>
                <small>Templates are saved to your account and available across candidate lists. Saving an existing name updates it.</small>
            </div>
            <label for="cc-message">Message</label><textarea id="cc-message" rows="8" required maxlength="10000"></textarea>
            <small>Use [applicant_name] to insert each candidate’s name. Each recipient receives a separate message.</small>
            <p id="cc-feedback" role="status" aria-live="polite"></p>
            <div class="cc-actions"><button type="button" id="cc-close">Close</button><button type="submit" id="cc-send">Send to selected candidates</button></div>
        </form>
    </dialog>
</div>
@push('head-script')
<style>
.cc-toolbar{display:flex;gap:12px;align-items:center;flex-wrap:wrap;padding:12px 16px;background:#fff;border:1px solid #dde2ea;border-radius:12px;margin-bottom:16px;font-size:13px}.cc-toolbar span,.cc-toolbar strong{color:#5a6478}.cc-toolbar button,#cc-dialog button{border:1px solid #cbd5e1;border-radius:8px;padding:8px 12px;background:#eff6ff;color:#1d4ed8;cursor:pointer}#cc-dialog{position:fixed;inset:0;margin:auto;width:min(620px,94vw);max-height:90vh;overflow:auto;border:1px solid #dde2ea;border-radius:16px;padding:24px;color:#1a1e2e;background:white;z-index:10000}#cc-dialog::backdrop{background:#0f172a99}#cc-dialog h2{font-size:20px;font-weight:700}#cc-dialog label{display:block;margin:12px 0 4px;font-weight:600}#cc-dialog input,#cc-dialog select,#cc-dialog textarea{width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:9px;background:white;color:#1a1e2e}#cc-dialog small{display:block;font-size:12px;color:#64748b;margin:6px 0}#cc-dialog details{margin:12px 0}#cc-recipients{max-height:140px;overflow:auto;font-size:12px}.cc-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px}#cc-feedback{margin-top:12px;white-space:pre-line}#cc-dialog button:disabled,.cc-toolbar button:disabled{opacity:.5;cursor:default}input[type=checkbox].cc-select,input[type=checkbox].cc-all{position:static!important;left:auto!important;opacity:1!important;appearance:auto!important;width:16px!important;height:16px!important;display:inline-block!important;margin-right:10px;cursor:pointer}
</style>
@endpush
@push('footer-script')
<script src="{{ asset('js/candidate-communications.js') }}"></script>
@endpush
@endif
