(function () {
    'use strict';
    function init(root) {
        if (!root || root.dataset.initialized === '1') return;
        root.dataset.initialized = '1';
        const automatic = root.dataset.autoRecord === '1';
        let durationTimer, recordingStarting = false, settingUp = false, cancelRequested = false;
        function updateDuration() {
            const seconds = Math.max(0, Math.floor((Date.now() - started) / 1000));
            if (el('duration')) el('duration').textContent = [Math.floor(seconds / 3600), Math.floor(seconds / 60) % 60, seconds % 60].map(value => String(value).padStart(2, '0')).join(':');
        }
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
            if (automatic) el('consent').hidden = false;
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
            clearInterval(durationTimer);
            if (el('recording')) el('recording').hidden = true;
            if (microphone) microphone.getTracks().forEach(track => track.stop());
            microphone = null;
            if (context) context.close().catch(() => {});
            context = null;
            if (client) { const closingClient = client; client = null; try { closingClient.disconnect(); } catch (_) {} }
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
                    if (automatic) el('consent').hidden = false;
                    refreshHistory();
                    status(error.message + ' You can retry from the History tab.');
                } else {
                    busy = false;
                    status(error.message + ' Reload this page to retry from history.');
                }
            }
        }
        async function finish(localHangup = false) {
            if (ending || !busy) return;
            ending = true;
            el('end').disabled = true;
            status('Ending call…');
            clearInterval(durationTimer);
            if (localHangup && call) {
                try { await call.hangup(); } catch (error) {
                    ending = false; el('end').disabled = false;
                    status('Could not end the call: ' + error.message + '. Try again.');
                    return;
                }
            }
            duration = started ? Math.min(14400, Math.round((Date.now() - started) / 1000)) : 0;
            if (recorder && recorder.state !== 'inactive') {
                await new Promise(resolve => { recorder.addEventListener('stop', resolve, {once: true}); recorder.stop(); });
            }
            if (recorder && chunks.length) recording = new Blob(chunks, {type: recorder.mimeType});
            chunks = [];
            cleanup();
            if (session) await save();
            else completeEmbedded('Call cancelled.');
        }
        if (el('retry-upload')) el('retry-upload').addEventListener('click', save);
        root._candidateCallStart = async () => {
            if (busy) return;
            busy = true;
            settingUp = true; cancelRequested = false; ending = false;
            el('end').disabled = false;
            if (el('duration')) el('duration').textContent = '00:00:00';
            status('Preparing call…');
            started = 0; duration = 0; muted = false; call = null; session = null; recorder = null; recording = null; chunks = [];
            if (!automatic) el('consent-check').checked = false;
            el('consent-check').disabled = automatic;
            el('record').disabled = false;
            el('consent').hidden = true;
            el('retry-upload').hidden = true;
            el('start').disabled = true;
            if (el('close')) el('close').disabled = true;
            try {
                if (!window.isSecureContext || !navigator.mediaDevices || !window.MediaRecorder) throw new Error('Calling requires HTTPS and microphone/recording support.');
                microphone = await navigator.mediaDevices.getUserMedia({audio: true});
                if (cancelRequested) { await finish(); return; }
                await ensureTelnyxSdk();
                if (cancelRequested) { await finish(); return; }
                session = await post(root.dataset.start);
                if (cancelRequested) { await finish(); return; }
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
                        el('consent').hidden = automatic;
                        status(automatic && !el('consent-check').checked ? 'Connected · Not recording' : 'Connected');
                        updateDuration();
                        durationTimer = setInterval(updateDuration, 1000);
                    }
                    if (call.state === 'active' && automatic && el('consent-check').checked && !recorder && !recordingStarting) {
                        startRecording();
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
                else { cleanup(); if (automatic) { el('consent').hidden = false; el('consent-check').disabled = false; } busy = false; el('start').disabled = false; if (el('close')) el('close').disabled = false; }
                status(error.message);
            } finally { settingUp = false; }
        };
        el('start').addEventListener('click', root._candidateCallStart);
        el('end').addEventListener('click', async () => {
            if (!busy || ending) return;
            if (settingUp) {
                cancelRequested = true;
                el('end').disabled = true;
                status('Cancelling call setup…');
                if (microphone) microphone.getTracks().forEach(track => track.stop());
                return;
            }
            await finish(true);
        });
        el('mute').addEventListener('click', () => {
            if (!call) return;
            muted = !muted;
            if (muted) call.muteAudio(); else call.unmuteAudio();
            el('mute').textContent = muted ? 'Unmute' : 'Mute';
        });
        async function startRecording() {
            if (!el('consent-check').checked) { status('Confirm the candidate’s agreement before recording.'); return; }
            if (!call || ending || recorder || recordingStarting) return;
            recordingStarting = true;
            el('record').disabled = true;
            try {
                const mimeType = ['audio/webm;codecs=opus', 'audio/mp4'].find(type => MediaRecorder.isTypeSupported(type));
                if (!mimeType) throw new Error('This browser cannot record a supported audio format.');
                if (!call.localStream?.getAudioTracks().length || !call.remoteStream?.getAudioTracks().length) throw new Error('Both sides must be connected before recording. Try again.');
                context = new (window.AudioContext || window.webkitAudioContext)();
                await context.resume();
                if (ending || !call) return;
                const mix = context.createMediaStreamDestination();
                context.createMediaStreamSource(call.localStream).connect(mix);
                context.createMediaStreamSource(call.remoteStream).connect(mix);
                recorder = new MediaRecorder(mix.stream, {mimeType, audioBitsPerSecond: 32000});
                recorder.addEventListener('dataavailable', event => { if (event.data.size) chunks.push(event.data); });
                recorder.start(1000);
                el('consent-check').disabled = true;
                if (el('recording')) el('recording').hidden = false;
                status('Connected');
                timer = setInterval(() => {
                    if (chunks.reduce((size, chunk) => size + chunk.size, 0) >= 23 * 1024 * 1024 || Date.now() - started >= 1800000) {
                        clearInterval(timer); recorder.stop();
                        if (el('recording')) el('recording').hidden = true;
                        status('Recording limit reached. Summary covers the recorded portion.');
                    }
                }, 1000);
            } catch (error) {
                if (context) { context.close().catch(() => {}); context = null; }
                recorder = null; el('record').disabled = false; status('Recording unavailable: ' + error.message);
            } finally { recordingStarting = false; }
        }
        el('record').addEventListener('click', startRecording);
        if (el('close')) el('close').addEventListener('click', () => {
            if (busy) { status('End the call and wait for it to save before closing.'); return; }
            window.jaCloseCallModal(root.dataset.applicationId);
        });
    }

    window.initCandidateCalls = init;
    window.jaOpenCallModal = function (applicationId) {
        const root = Array.from(document.querySelectorAll('[id="candidate-calls"]')).find(node => node.dataset.applicationId === String(applicationId));
        if (!root) return;
        init(root);
        if (!root._jaOriginalParent) root._jaOriginalParent = root.parentNode;
        if (root.parentNode !== document.body) document.body.appendChild(root);
        root.classList.remove('hidden'); root.classList.add('flex'); root.style.display = 'flex';
        const start = root.querySelector('#call-start');
        if (start && !start.disabled) start.focus();
    };
    window.jaCloseCallModal = function (applicationId) {
        const root = Array.from(document.querySelectorAll('[id="candidate-calls"]')).find(node => node.dataset.applicationId === String(applicationId));
        if (!root) return;
        root.classList.add('hidden'); root.classList.remove('flex'); root.style.display = 'none';
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
