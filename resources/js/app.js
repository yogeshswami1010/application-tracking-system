// Main JavaScript entry point
import './bootstrap';

// jQuery is loaded from CDN in the layout, so we just ensure it's available
if (typeof window.$ === 'undefined' && typeof jQuery !== 'undefined') {
    window.$ = window.jQuery = jQuery;
}

// Import lodash and make it globally available
import _ from 'lodash';
window._ = _;

// Import axios and make it globally available
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add any custom JavaScript here
import swal from 'sweetalert2';
window.Swal = swal;

// froiden-helper attaches $.easyAjax to jQuery; keep $ on the same instance for legacy Blade scripts
// that run after this module (e.g. Update Application Swal.then → $.easyAjax).
if (typeof window.jQuery !== 'undefined') {
    window.$ = window.jQuery;
}

// Targeted ATS real-time updates (no full-page reload).
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbConfig = window.ATS_REVERB_CONFIG || {};
const reverbKey = reverbConfig.key || import.meta.env.VITE_REVERB_APP_KEY;
if (reverbKey) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbConfig.host || import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: Number(reverbConfig.port || import.meta.env.VITE_REVERB_PORT || 80),
        wssPort: Number(reverbConfig.port || import.meta.env.VITE_REVERB_PORT || 443),
        forceTLS: (reverbConfig.scheme || import.meta.env.VITE_REVERB_SCHEME || 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: `${window.appBaseUrl || ''}/broadcasting/auth`,
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
        },
    });

    let tableRefreshTimer = null;
    let locallyDirty = false;

    document.addEventListener('input', (event) => {
        if (event.target.closest('[data-ats-application-id] form, [data-ats-consortium-id] form')) locallyDirty = true;
    }, true);
    document.addEventListener('change', (event) => {
        if (event.target.closest('[data-ats-application-id] form, [data-ats-consortium-id] form')) locallyDirty = true;
    }, true);
    document.addEventListener('submit', () => { locallyDirty = false; }, true);

    const showRealtimeNotice = (event, conflict = false) => {
        document.querySelector('[data-ats-realtime-notice]')?.remove();
        const notice = document.createElement('div');
        notice.dataset.atsRealtimeNotice = '1';
        notice.setAttribute('role', 'status');
        notice.style.cssText = 'position:fixed;right:24px;bottom:24px;z-index:99999;max-width:390px;padding:14px 16px;border-radius:14px;background:#fff;border:1px solid #dbe4f0;box-shadow:0 16px 42px rgba(15,23,42,.18);font:500 13px/1.45 sans-serif;color:#172033';
        const actor = event.actor_name || 'Another team member';
        notice.innerHTML = `<div style="display:flex;gap:12px;align-items:flex-start"><span style="width:9px;height:9px;margin-top:5px;border-radius:50%;background:${conflict ? '#f59e0b' : '#10b981'};flex:none"></span><div style="flex:1"><strong style="display:block;margin-bottom:3px">${conflict ? 'Newer profile change available' : 'ATS updated'}</strong><span>${actor} updated this candidate${conflict ? '. Your unsaved work was not changed.' : '.'}</span></div><button type="button" aria-label="Close" style="border:0;background:transparent;font-size:18px;line-height:1;cursor:pointer;color:#64748b">&times;</button></div>`;
        notice.querySelector('button').addEventListener('click', () => notice.remove());
        document.body.appendChild(notice);
        if (!conflict) window.setTimeout(() => notice.remove(), 6000);
    };

    window.Echo.private('ats.updates').listen('.candidate.updated', (event) => {
        const currentUserId = Number(window.ATS_CURRENT_USER_ID || 0);
        if (event.actor_id && Number(event.actor_id) === currentUserId) return;

        document.dispatchEvent(new CustomEvent('ats:candidate-updated', { detail: event }));

        if (locallyDirty) {
            showRealtimeNotice(event, true);
            return;
        }

        // Keep filters, pagination and scroll. AI Search is deliberately excluded.
        if (!window.location.pathname.includes('/ai-search') && window.jQuery?.fn?.dataTable) {
            window.clearTimeout(tableRefreshTimer);
            tableRefreshTimer = window.setTimeout(() => {
                const api = window.jQuery.fn.dataTable.tables({ visible: true, api: true });
                if (api?.ajax) api.ajax.reload(null, false);
            }, 350);
        }

        showRealtimeNotice(event, false);
    });
}