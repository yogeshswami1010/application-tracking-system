(function () {
    'use strict';
    function init(root) {
        if (!root || root.dataset.initialized === '1') return;
        root.dataset.initialized = '1';
        const embedded = root.dataset.embedded === '1';
        const el = id => root.querySelector('#call-' + id);
        const status = message => { if (el('status')) el('status').textContent = message; };
        let client, call, session, recorder, context, microphone, timer;
        let chunks = [], recording = null, started = 0, duration = 0, busy = false, ending = false, muted = false, allowNavigation = false;

        async function post(url, data) {
            const response = await fetch(url, {method: 'POST', credentials: 'same-origin', headers: {
                'X-CSRF-TOKEN': root.dataset.token, 'Accept': 'application/json'
            }, body: data});
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || ('Request failed (HTTP ' + response.status + '). Please try again.'));
            return result;
        }
        async function ensureTelnyxSdk() {
            if (window.TelnyxWebRTC) return;
            status('Loading the secure calling service…');
            if (!window._jaTelnyxSdkPromise) {
                window._jaTelnyxSdkPromise = new Promise(function (resolve, reject) {
                    const script = document.createElement('script');
                    script.async = true;
                    script.src = 'https://unpkg.com/@telnyx/webrtc@2.9.0/lib/bundle.js';
                    script.onload = resolve;
                    script.onerror = () => reject(new Error('The calling library could not load. Check your connection and try again.'));
                    document.head.appendChild(script);
                });
            }
            await window._jaTelnyxSdkPromise;
            if (!window.TelnyxWebRTC) throw new Error('The calling library loaded without its browser API. Reload and try again.');
        }
        function refreshHistory() {
            if (typeof window.jaRefreshCandidateCallHistory === 'function') window.jaRefreshCandidateCallHistory(root.dataset.applicationId);
        }
        function completeEmbedded(message) {
            status(message);
            busy = false; ending = false; muted = false; call = null; session = null; recorder = null; recording = null; chunks = []; started = 0; duration = 0;
            el('start').disabled = false;
            el('end').disabled = true;
            el('mute').disabled = true;
            el('mute').textContent = 'Mute';
            el('record').disabled = false;
            el('consent-check').disabled = false;
            el('consent-check').checked = false;
            if (el('close')) el('close').disabled = false;
            refreshHistory();
        }
        async function process(url) {
            status('Recording saved. Generating summary…');
            await post(url);
            if (embedded) { completeEmbedded('Call summary saved.'); return; }
            allowNavigation = true;
            window.location.reload();
        }
        root._candidateCallProcess = process;
        if (!el('start')) return;
        el('start').disabled = false;
        window.addEventListener('beforeunload', event => {
            if (busy && !allowNavigation) { event.preventDefault(); event.returnValue = ''; }
        });
        function cleanup() {
            clearInterval(timer);
            if (microphone) microphone.getTracks().forEach(track => track.stop());
            microphone = null;
            if (context) context.close().catch(() => {});
            context = null;
            if (client) { client.disconnect(); client = null; }
            el('end').disabled = true;
            el('mute').disabled = true;
            el('consent').hidden = true;
        }
        async function save() {
            el('retry-upload').hidden = true;
            status('Saving call…');
            const data = new FormData();
            data.append('duration_seconds', String(duration));
            if (recording) {
                data.append('recording_consent', '1');
                data.append('audio', recording, recording.type.includes('mp4') ? 'call.mp4' : 'call.webm');
            }
            let result;
            try { result = await post(root.dataset.start + '/' + session.id + '/finish', data); }
            catch (error) {
                status(error.message + ' Keep this page open and retry saving.');
                el('retry-upload').hidden = false;
                return;
            }
            recording = null;
            if (embedded) refreshHistory();
            try {
                if (result.status === 'pending' || result.status === 'failed') {
                    await process(root.dataset.start + '/' + session.id + '/process');
                } else if (embedded) {
                    completeEmbedded(result.status === 'not_recorded' ? 'Call saved. No recording or summary was made.' : 'Call saved.');
                } else {
                    allowNavigation = true;
                    window.location.reload();
                }
            } catch (error) {
                if (embedded) {
                    busy = false; ending = false;
                    call = null; session = null; recorder = null; recording = null; chunks = []; started = 0; duration = 0;
                    el('start').disabled = false;
                    el('end').disabled = true;
                    el('mute').disabled = true;
                    el('record').disabled = false;
                    el('consent-check').disabled = false;
                    el('consent-check').checked = false;
                    if (el('close')) el('close').disabled = false;
                    refreshHistory();
                    status(error.message + ' You can retry from the History tab.');
                } else {
                    busy = false;
                    status(error.message + ' Reload this page to retry from history.');
                }
            }
        }
        async function finish() {
            if (ending) return;
            ending = true;
            duration = started ? Math.min(14400, Math.round((Date.now() - started) / 1000)) : 0;
            if (recorder && recorder.state !== 'inactive') {
                await new Promise(resolve => { recorder.addEventListener('stop', resolve, {once: true}); recorder.stop(); });
            }
            if (recorder && chunks.length) recording = new Blob(chunks, {type: recorder.mimeType});
            chunks = [];
            cleanup();
            await save();
        }
        if (el('retry-upload')) el('retry-upload').addEventListener('click', save);
        el('start').addEventListener('click', async () => {
            if (busy) return;
            busy = true;
            started = 0; duration = 0; muted = false; call = null; session = null; recorder = null; recording = null; chunks = [];
            el('consent-check').checked = false;
            el('consent-check').disabled = false;
            el('record').disabled = false;
            el('consent').hidden = true;
            el('retry-upload').hidden = true;
            el('start').disabled = true;
            if (el('close')) el('close').disabled = true;
            try {
                if (!window.isSecureContext || !navigator.mediaDevices || !window.MediaRecorder) throw new Error('Calling requires HTTPS and microphone/recording support.');
                microphone = await navigator.mediaDevices.getUserMedia({audio: true});
                await ensureTelnyxSdk();
                session = await post(root.dataset.start);
                client = new window.TelnyxWebRTC.TelnyxRTC({login_token: session.token});
                client.remoteElement = 'call-remote';
                client.on('telnyx.ready', () => {
                    if (call || ending) return;
                    call = client.newCall({destinationNumber: session.phone, callerNumber: session.from, audio: true, video: false, localStream: microphone});
                    el('end').disabled = false;
                    status('Calling candidate…');
                });
                client.on('telnyx.notification', notification => {
                    if (notification.type !== 'callUpdate' || !call || notification.call.id !== call.id) return;
                    call = notification.call;
                    if (['trying', 'requesting', 'early'].includes(call.state)) status('Calling candidate…');
                    if (call.state === 'ringing') status('Ringing candidate…');
                    if (call.state === 'active' && !started) {
                        started = Date.now();
                        el('mute').disabled = false;
                        el('consent').hidden = false;
                        status('Connected. Ask the candidate for recording consent before recording.');
                    }
                    if (['hangup', 'destroy', 'purge'].includes(call.state)) finish();
                });
                client.on('telnyx.error', () => {
                    status('The calling connection failed.');
                    if (call) { try { call.hangup(); } catch (_) {} }
                    finish();
                });
                status('Connecting to calling service…');
                client.connect();
                setTimeout(() => { if (!call && !ending && busy) { status('Connection timed out.'); finish(); } }, 30000);
            } catch (error) {
                if (session) await finish();
                else { cleanup(); busy = false; el('start').disabled = false; if (el('close')) el('close').disabled = false; }
                status(error.message);
            }
        });
        el('end').addEventListener('click', () => { if (call) { try { call.hangup(); } catch (_) {} } finish(); });
        el('mute').addEventListener('click', () => {
            if (!call) return;
            muted = !muted;
            if (muted) call.muteAudio(); else call.unmuteAudio();
            el('mute').textContent = muted ? 'Unmute' : 'Mute';
        });
        el('record').addEventListener('click', async () => {
            if (!el('consent-check').checked) { status('Confirm the candidate’s agreement before recording.'); return; }
            if (!call || ending || recorder) return;
            el('record').disabled = true;
            try {
                const mimeType = ['audio/webm;codecs=opus', 'audio/mp4'].find(type => MediaRecorder.isTypeSupported(type));
                if (!mimeType) throw new Error('This browser cannot record a supported audio format.');
                if (!call.localStream?.getAudioTracks().length || !call.remoteStream?.getAudioTracks().length) throw new Error('Both sides must be connected before recording. Try again.');
                context = new (window.AudioContext || window.webkitAudioContext)();
                await context.resume();
                const mix = context.createMediaStreamDestination();
                context.createMediaStreamSource(call.localStream).connect(mix);
                context.createMediaStreamSource(call.remoteStream).connect(mix);
                recorder = new MediaRecorder(mix.stream, {mimeType, audioBitsPerSecond: 32000});
                recorder.addEventListener('dataavailable', event => { if (event.data.size) chunks.push(event.data); });
                recorder.start(1000);
                el('consent-check').disabled = true;
                status('Recording both sides for the summary. Recording stops after 30 minutes.');
                timer = setInterval(() => {
                    if (chunks.reduce((size, chunk) => size + chunk.size, 0) >= 23 * 1024 * 1024 || Date.now() - started >= 1800000) {
                        clearInterval(timer); recorder.stop();
                        status('Recording limit reached. Summary covers the recorded portion.');
                    }
                }, 1000);
            } catch (error) {
                if (context) { context.close().catch(() => {}); context = null; }
                recorder = null; el('record').disabled = false; status(error.message);
            }
        });
        if (el('close')) el('close').addEventListener('click', () => {
            if (busy) { status('End the call and wait for it to save before closing.'); return; }
            window.jaCloseCallModal(root.dataset.applicationId);
        });
    }

    window.initCandidateCalls = init;
    window.jaOpenCallModal = function (applicationId) {
        const root = document.getElementById('candidate-calls');
        if (!root || root.dataset.applicationId !== String(applicationId)) return;
        init(root);
        if (!root._jaOriginalParent) root._jaOriginalParent = root.parentNode;
        if (root.parentNode !== document.body) document.body.appendChild(root);
        root.classList.remove('hidden'); root.classList.add('flex');
        const start = root.querySelector('#call-start');
        if (start && !start.disabled) start.focus();
    };
    window.jaCloseCallModal = function (applicationId) {
        const root = document.getElementById('candidate-calls');
        if (!root || root.dataset.applicationId !== String(applicationId)) return;
        root.classList.add('hidden'); root.classList.remove('flex');
        if (root._jaOriginalParent && root.parentNode === document.body) root._jaOriginalParent.appendChild(root);
    };
    document.addEventListener('click', event => {
        const button = event.target.closest('.call-process');
        if (!button || button.disabled) return;
        const root = document.getElementById('candidate-calls');
        if (!root || !root._candidateCallProcess) return;
        button.disabled = true;
        root._candidateCallProcess(button.dataset.url).catch(error => {
            const status = root.querySelector('#call-status');
            if (status) status.textContent = error.message;
            button.disabled = false;
        });
    });
    const root = document.getElementById('candidate-calls');
    if (root) init(root);
})();
