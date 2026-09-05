<script>
(function () {
    if (window._jaProfileSyncTimer) window.clearInterval(window._jaProfileSyncTimer);
    if (window._jaProfileVisibilityHandler) {
        document.removeEventListener('visibilitychange', window._jaProfileVisibilityHandler);
    }

    var appId = {{ (int) $application->id }};
    var urlTemplate = @json(route('admin.job-applications.profile-tab', [$application->id, '__TAB__']));
    var inFlight = false;
    var lastHtml = {};

    function activeProfileTab() {
        var active = document.querySelector('.ja-right-panel .ja-tabs .ja-tab.active');
        return active ? active.getAttribute('data-tab') : null;
    }

    function targetFor(tab) {
        if (tab === 'notes') return document.getElementById('applicant-notes');
        if (tab === 'client-notes') return document.getElementById('client-notes-list');
        if (tab === 'sms') {
            return document.getElementById('ja-sms-history-' + appId)
                || document.getElementById('ja-shared-sms-history-' + appId);
        }
        return null;
    }

    function isEditing(target) {
        if (!target) return false;
        var focused = document.activeElement;
        return !!(focused && target.contains(focused)
            && /^(INPUT|TEXTAREA|SELECT)$/.test(focused.tagName));
    }

    function syncActiveTab() {
        if (inFlight || document.hidden) return;
        var tab = activeProfileTab();
        var target = targetFor(tab);
        if (!target || isEditing(target)) return;

        inFlight = true;
        $.ajax({
            url: urlTemplate.replace('__TAB__', tab),
            type: 'GET',
            cache: false,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).done(function (response) {
            if (!response || response.status !== 'success' || typeof response.view !== 'string') return;
            var currentTarget = targetFor(tab);
            if (!currentTarget || isEditing(currentTarget) || lastHtml[tab] === response.view) return;

            var oldHeight = currentTarget.scrollHeight;
            var nearBottom = currentTarget.scrollTop + currentTarget.clientHeight >= oldHeight - 40;
            currentTarget.innerHTML = response.view;
            lastHtml[tab] = response.view;

            if (tab === 'sms') {
                var countText = Number(response.count || 0) + ' messages';
                var countEl = document.getElementById('ja-sms-history-count-' + appId)
                    || document.getElementById('ja-shared-sms-count-' + appId);
                if (countEl) countEl.textContent = countText;
                if (nearBottom) currentTarget.scrollTop = currentTarget.scrollHeight;
            }
        }).always(function () {
            inFlight = false;
        });
    }

    window._jaProfileSyncTimer = window.setInterval(syncActiveTab, 7000);
    $(document).off('click.jaProfileSync', '.ja-tab')
        .on('click.jaProfileSync', '.ja-tab', function () {
            window.setTimeout(syncActiveTab, 250);
        });

    window._jaProfileVisibilityHandler = function () {
        if (!document.hidden) syncActiveTab();
    };
    document.addEventListener('visibilitychange', window._jaProfileVisibilityHandler);
})();
</script>