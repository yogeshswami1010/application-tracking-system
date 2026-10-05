const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const source = fs.readFileSync('resources/views/admin/job-applications/partials/client-review-scripts.blade.php', 'utf8').replace(/^<script>\s*/, '').replace(/<\/script>\s*$/, '');
const config = {token:'csrf-test', indexUrl:'/reviews', unreadUrl:'/unread', sendUrl:'/send', replyUrl:'/reviews/__REVIEW__/reply', revokeUrl:'/reviews/__REVIEW__/revoke', retryUrl:'/reviews/__REVIEW__/messages/__MESSAGE__/retry'};
const requests = [], events = {}, cleared = [];
const list = {dataset:{}, innerHTML:'', querySelector:()=>null};
const panel = {isConnected:true, contains:()=>true};
const badge = {style:{}, textContent:''};
const pane = {style:{display:'none'}};
const editor = {id:'compose', innerHTML:'<p><b>Hello</b> José</p><ul><li>Experience</li></ul>', textContent:'Hello José Experience', dataset:{}, contentEditable:'true'};
const output = {style:{}, textContent:''};
const control = {disabled:false};
const form = {dataset:{}, elements:{client_email:{value:'client@example.test'}, subject:{value:'Review candidate'}},
    reportValidity:()=>true, getAttribute:()=>null,
    querySelector: selector=>selector==='.ja-review-editor'?editor:output,
    querySelectorAll:()=>[control], reset(){this.resetCalled=true;}};
const nodes = {'ja-client-reviews-panel':panel, 'ja-client-review-config':{textContent:JSON.stringify(config)}, 'ja-client-review-conversations':list, 'ja-client-review-unread':badge, 'ja-tab-client-reviews':pane};
let tick;
function $() { return {off:()=>({}), on(event, selector, fn){ events[event+' '+selector]=fn; return this; }}; }
// .off() is called independently; .on() calls return a chain only for consistency.
$.ajax = options => {
    const request = {options, done(fn){this.success=fn;return this;}, fail(fn){this.failure=fn;return this;}, always(fn){this.final=fn;return this;},
        resolve(data){this.success(data);if(this.final)this.final();}, reject(data){if(this.failure)this.failure(data);if(this.final)this.final();}};
    requests.push(request); return request;
};
const context = {$, TextEncoder, btoa:s=>Buffer.from(s,'binary').toString('base64'), Math,
    document:{hidden:false, getElementById:id=>nodes[id], addEventListener(){}, removeEventListener(){}},
    crypto:{randomUUID:()=> '00000000-0000-4000-8000-000000000001'},
    setInterval(fn){tick=fn;return 19;},clearInterval:id=>cleared.push(id), _jaClientReviewTimer:18,
    _jaProfileCache:{clear(){this.cleared=true;}}};
context.window=context;
vm.createContext(context);
vm.runInContext(source,context);
assert(cleared.includes(18),'Clear old profile polling');
requests[0].resolve({unread:1});
assert.equal(badge.textContent,'1 new');
assert.equal(badge.style.display,'inline-flex');
pane.style.display='block';tick();
assert.equal(requests[1].options.url,'/reviews');
requests[1].resolve({view:'Client reply conversation'});
assert.equal(list.innerHTML,'Client reply conversation');
assert.equal(badge.style.display,'none');
const submit = events['submit.jaClientReviews [data-client-review-send], [data-client-review-reply]'];
const event = {preventDefault(){}};
submit.call(form,event);
submit.call(form,event);
assert.equal(requests.length,3,'Only one send request while pending');
assert.equal(requests[2].options.data.client_email,'client@example.test');
assert.equal(requests[2].options.data._token,'csrf-test');
assert.equal(Buffer.from(requests[2].options.data.message_payload,'base64').toString('utf8'),editor.innerHTML,'Encode Unicode rich text intact');
requests[2].resolve({message:'Profile email sent'});
assert(form.resetCalled && !control.disabled && editor.contentEditable==='true');
assert.equal(editor.innerHTML,'');
assert(context._jaProfileCache.cleared);
requests[3].resolve({view:'Sent invitation'});
list.querySelector=()=>({dataset:{reviewDirty:'1'}});
tick();
assert.equal(requests.length,4,'Do not refresh over an unsent reply draft');
list.querySelector=()=>null;
editor.innerHTML='<p>Follow-up</p>';editor.textContent='Follow-up';
form.getAttribute=()=> '15';
submit.call(form,event);
assert.equal(requests[4].options.url,'/reviews/15/reply');
requests[4].reject({responseJSON:{errors:{message_payload:['Write a valid message.']}}});
assert.equal(form.dataset.busy,'0');
assert.equal(output.textContent,'Write a valid message.');
assert.equal(editor.innerHTML,'<p>Follow-up</p>','Keep the draft on send failure');
requests[5].resolve({view:'Sent invitation'});
editor.dataset.reviewDirty='1';
list.querySelector=()=>editor.dataset.reviewDirty==='1'?editor:null;
submit.call(form,event);
requests[6].reject({responseJSON:{message:'SMTP unavailable',saved:true}});
assert.equal(editor.dataset.reviewDirty,'0','A stored failed reply must reveal its retry action');
assert.equal(editor.innerHTML,'','The failed content is retained in the server conversation');
requests[7].resolve({view:'Failed message with Retry email'});
assert.equal(list.innerHTML,'Failed message with Retry email');
const retry = events['click.jaClientReviews [data-client-review-retry], [data-client-review-revoke]'];
const retryButton = {disabled:false,dataset:{message:'77'},getAttribute:key=>key==='data-client-review-retry'?'15':null,closest:()=>({querySelector:()=>output})};
retry.call(retryButton);
retry.call(retryButton);
assert.equal(requests.length,9,'Only one retry request while pending');
assert.equal(requests[8].options.url,'/reviews/15/messages/77/retry');
requests[8].resolve({message:'Email sent'});
requests[9].resolve({view:'Email sent'});
assert(!retryButton.disabled);
panel.isConnected=false;tick();
assert(cleared.includes(19),'Closing the profile stops polling');
console.log('PASS: client-review rich text, email submission, validation feedback, draft preservation, live replies, unread badge, and timer cleanup');
