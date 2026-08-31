@if($user->cans('edit_job_applications'))
    <input type="file" id="ja-resume-upload-{{ $application->id }}" accept=".pdf,.doc,.docx,.rtf,.txt,.jpg,.jpeg,.png" hidden>
    <button type="button" id="ja-resume-update-btn-{{ $application->id }}" class="ja-pdf-btn"
            onclick="document.getElementById('ja-resume-upload-{{ $application->id }}').click()">
        <i class="fa fa-upload"></i> <span>{{ $application->resume_url ? 'Update CV' : 'Upload CV' }}</span>
    </button>
    <script>
    (function () {
        var input = document.getElementById('ja-resume-upload-{{ $application->id }}');
        var button = document.getElementById('ja-resume-update-btn-{{ $application->id }}');
        if (!input || !button) return;

        input.addEventListener('change', function () {
            if (!input.files.length) return;
            var data = new FormData();
            data.append('_token', @json(csrf_token()));
            data.append('resume', input.files[0]);
            button.disabled = true;
            button.querySelector('span').textContent = 'Uploading...';

            fetch(@json(route('admin.job-applications.update-resume', $application->id)), {
                method: 'POST', body: data, headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).then(function (response) {
                return response.json().then(function (json) {
                    if (!response.ok) throw json;
                    return json;
                });
            }).then(function (response) {
                document.querySelectorAll('.ja-current-resume-link').forEach(function (link) {
                    link.href = response.resume_url;
                });
                var frame = document.getElementById('ja-pdf-frame');
                if (frame) {
                    frame.src = response.resume_url + '#view=FitH';
                    frame.style.display = 'block';
                    var scroll = document.getElementById('ja-pdf-scroll');
                    var loading = document.getElementById('ja-pdf-loading');
                    var error = document.getElementById('ja-pdf-error');
                    if (scroll) scroll.style.display = 'none';
                    if (loading) loading.style.display = 'none';
                    if (error) error.style.display = 'none';
                } else {
                    var embed = document.querySelector('embed.ja-pdf-frame');
                    if (embed) embed.src = response.resume_url;
                }
                var empty = document.querySelector('.ja-pdf-no-resume');
                if (empty) empty.innerHTML = '<i class="fa fa-check-circle" style="color:#10B981"></i><p>CV uploaded. Reopen this profile to view it.</p>';
                var history = $('#ja-tab-history');
                if (history.length && history.data('url')) {
                    history.removeData('loaded').html('<div class="ja-tab-loading">Open the History tab to load activity.</div>');
                } else if (history.length && response.history_html) {
                    var oldResumeHistory = history.find('#ja-resume-history-card');
                    if (oldResumeHistory.length) oldResumeHistory.replaceWith(response.history_html);
                    else history.prepend(response.history_html);
                }
                button.querySelector('span').textContent = 'Update CV';
                if (typeof toastr !== 'undefined') toastr.success(response.message);
            }).catch(function (error) {
                var message = error.message || (error.errors && error.errors.resume && error.errors.resume[0]) || 'The CV could not be updated.';
                if (typeof toastr !== 'undefined') toastr.error(message); else alert(message);
            }).finally(function () {
                button.disabled = false;
                input.value = '';
                if (button.querySelector('span').textContent === 'Uploading...') button.querySelector('span').textContent = 'Update CV';
            });
        });
    })();
    </script>
@endif
