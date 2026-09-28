(function () {
    'use strict';
    const root = document.getElementById('candidate-calls');
    if (!root) return;
    const el = id => document.getElementById('call-' + id);
    const status = message => { if (el('status')) el('status').textContent = message; };
    let client, call, session, recorder, context, microphone, timer;
    let chunks = [], recording = null, started = 0, duration = 0, busy = false, ending = false, muted = false;
    async function post(url, data) {
        const response = await fetch(url, {method: 'POST', credentials: 'same-origin', headers: {
            'X-CSRF-TOKEN': root.dataset.token, 'Accept': 'application/json'
        }, body: data});
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(result.message || 'Request failed. Please try again.');
        return result;
    }
    async function process(url) {
        status('Recording saved. Generating summary…');
        await post(url);
        window.location.reload();
    }
    root.querySelectorAll('.call-process').forEach(button => button.addEventListener('click', async () => {
        if (busy) return;
        busy = true;
        button.disabled = true;
        try { await process(button.dataset.url); }
        catch (error) { status(error.message); button.disabled = false; busy = false; }
    }));
    if (!el('start')) return;
    window.addEventListener('beforeunload', event => {
        if (busy) { event.preventDefault(); event.returnValue = ''; }
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
        try {
            if (result.status === 'pending' || result.status === 'failed') {
                await process(root.dataset.start + '/' + session.id + '/process');
            } else window.location.reload();
        } catch (error) {
            busy = false;
            status(error.message + ' Reload this page to retry from history.');
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
    el('retry-upload').addEventListener('click', save);
    el('start').addEventListener('click', async () => {
        if (busy) return;
        busy = true;
        el('start').disabled = true;
        try {
            if (!window.isSecureContext || !navigator.mediaDevices || !window.MediaRecorder) {
                throw new Error('Calling requires HTTPS and a browser with microphone and recording support.');
            }
            if (!window.TelnyxWebRTC) throw new Error('The calling library could not load. Reload and try again.');
            microphone = await navigator.mediaDevices.getUserMedia({audio: true});
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
                if (call.state === 'active' && !started) {
                    started = Date.now();
                    el('mute').disabled = false;
                    el('consent').hidden = false;
                    status('Connected. Ask for recording consent before starting the summary recording.');
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
            setTimeout(() => {
                if (!call && !ending && busy) { status('Connection timed out.'); finish(); }
            }, 30000);
        } catch (error) {
            if (session) { await finish(); }
            else { cleanup(); busy = false; el('start').disabled = false; }
            status(error.message);
        }
    });
    el('end').addEventListener('click', () => {
        if (call) { try { call.hangup(); } catch (_) {} }
        finish();
    });
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
            if (!call.localStream?.getAudioTracks().length || !call.remoteStream?.getAudioTracks().length) {
                throw new Error('Both sides of the call must be connected before recording. Try again.');
            }
            context = new (window.AudioContext || window.webkitAudioContext)();
            await context.resume();
            const mix = context.createMediaStreamDestination();
            context.createMediaStreamSource(call.localStream).connect(mix);
            context.createMediaStreamSource(call.remoteStream).connect(mix);
            recorder = new MediaRecorder(mix.stream, {mimeType, audioBitsPerSecond: 32000});
            recorder.addEventListener('dataavailable', event => { if (event.data.size) chunks.push(event.data); });
            recorder.start(1000);
            el('consent-check').disabled = true;
            status('Recording both sides for your automatic summary. Recording stops after 30 minutes.');
            timer = setInterval(() => {
                if (chunks.reduce((size, chunk) => size + chunk.size, 0) >= 23 * 1024 * 1024 || Date.now() - started >= 1800000) {
                    clearInterval(timer);
                    recorder.stop();
                    status('Recording limit reached. You can continue talking; the summary will cover the recorded portion.');
                }
            }, 1000);
        } catch (error) {
            if (context) { context.close().catch(() => {}); context = null; }
            recorder = null;
            el('record').disabled = false;
            status(error.message);
        }
    });
})();
