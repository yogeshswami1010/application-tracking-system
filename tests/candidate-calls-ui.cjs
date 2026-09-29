const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('public/js/candidate-calls.js', 'utf8');

async function scenario({consent = true, capped = false, uploadFailure = false} = {}) {
    const elements = {};
    for (const id of ['start','mute','end','consent','consent-check','record','status','retry-upload']) {
        elements['call-' + id] = {disabled:false, hidden:true, checked:consent, listeners:{}, addEventListener(name, fn) {this.listeners[name] = fn;}};
    }
    const root = {dataset:{start:'/calls',token:'csrf'}, querySelectorAll:()=>[]};
    const events = {}, requests = [], timers = [];
    let beforeUnload;
    let rtc, reloads = 0, stops = 0, fail = uploadFailure;
    const stream = {getAudioTracks:()=>[{}],getTracks:()=>[{stop:()=>stops++}]};
    class Recorder {
        static isTypeSupported() {return true;}
        constructor(stream, options) {this.mimeType = options.mimeType; this.handlers = {}; this.state='inactive';}
        addEventListener(name, fn) {this.handlers[name] = fn;}
        start() {this.state='recording';}
        stop() {this.state='inactive'; this.handlers.dataavailable({data:new Blob(['audio'])}); if (this.handlers.stop) this.handlers.stop();}
    }
    class RTC {
        constructor() {rtc = this;}
        on(name, fn) {events[name]=fn;}
        connect() {events['telnyx.ready']();}
        disconnect() {}
        newCall() {this.call={id:'test',localStream:stream,remoteStream:stream,hangup(){},muteAudio(){this.muted=true;},unmuteAudio(){this.muted=false;}}; return this.call;}
    }
    const sandbox = {
        document:{getElementById:id=>id === 'candidate-calls' ? root : elements[id]},
        window:{isSecureContext:true,MediaRecorder:Recorder,TelnyxWebRTC:{TelnyxRTC:RTC},addEventListener(name, fn){if(name==='beforeunload')beforeUnload=fn;},location:{reload:()=>reloads++},AudioContext:class {
            resume(){return Promise.resolve();} close(){return Promise.resolve();}
            createMediaStreamDestination(){return {stream};} createMediaStreamSource(){return {connect(){}};}
        }},
        navigator:{mediaDevices:{getUserMedia:async()=>stream}},MediaRecorder:Recorder,Blob,FormData,
        setTimeout(){},setInterval(fn){timers.push(fn); return 1;},clearInterval(){},
        Date:{now:()=>capped && timers.length ? 2000000 : 1000},
        fetch:async(url,options)=>{
            requests.push({url,options});
            if (url.endsWith('/finish') && fail) {fail=false; throw new Error('offline');}
            return {ok:true,json:async()=>url === '/calls' ? {id:1,token:'token',phone:'+14165550100',from:'+14165550101'} : {status:consent?'pending':'not_recorded'}};
        }
    };
    vm.runInNewContext(source, sandbox);
    const click = id => elements['call-'+id].listeners.click();
    await click('start');
    rtc.call.state = 'active'; events['telnyx.notification']({type:'callUpdate',call:rtc.call});
    await click('mute'); assert.equal(rtc.call.muted,true);
    await click('record');
    if (!consent) assert.match(elements['call-status'].textContent,/agreement/);
    if (capped) timers[0]();
    await click('end');
    // End button triggers async finish through event handlers.
    for (let i=0;i<20;i++) await Promise.resolve();
    if (uploadFailure) {
        assert.equal(elements['call-retry-upload'].hidden,false);
        assert.equal(reloads,0);
        let prevented = false;
        beforeUnload({preventDefault(){prevented=true;}});
        assert.equal(prevented,true);
        await click('retry-upload');
    }
    const upload = requests.find(r=>r.url.endsWith('/finish')).options.body;
    assert.equal(upload.has('audio'),consent);
    assert.equal(upload.has('recording_consent'),consent);
    assert.equal(requests.some(r=>r.url.endsWith('/process')),consent);
    assert.equal(reloads,1);
    let prevented = false;
    beforeUnload({preventDefault(){prevented=true;}});
    assert.equal(prevented,false);
    assert.equal(stops,1);
}
(async()=>{
    await scenario(); console.log('PASS: connected call records, uploads, summarizes and releases microphone');
    await scenario({consent:false}); console.log('PASS: no consent means no recording or AI request');
    await scenario({capped:true}); console.log('PASS: capped recording is preserved on hangup');
    await scenario({uploadFailure:true}); console.log('PASS: upload failure retains audio for retry');
})().catch(error=>{console.error(error);process.exitCode=1;});
