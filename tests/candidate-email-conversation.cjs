const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const view = fs.readFileSync('resources/views/admin/job-applications/show.blade.php', 'utf8');
const code = view.slice(view.indexOf('window.jaLoadCandidateEmailConversation ='), view.indexOf('window.jaRefreshCandidateCallHistory ='))
    .replaceAll("{{ route('admin.candidate-communications.email', ':id') }}", '/email/:id')
    .replaceAll("{{ route('admin.candidate-communications.email.unread', ':id') }}", '/email/:id/unread')
    .replaceAll('{{ $application->id }}', '3280');
const box = {isConnected: true, scrollHeight: 400, scrollTop: 300, clientHeight: 100, innerHTML: ''};
const badge = {style: {}, textContent: ''};
const pane = {style: {display: 'none'}};
const nodes = {'ja-email-conversation-3280': box, 'ja-email-unread-3280': badge, 'ja-tab-email-conversation': pane};
const data = new WeakMap();
const requests = [];
const cleared = [];
let tick;
function $(node) {
    if (!data.has(node)) data.set(node, {});
    return {data(key, value) { if (arguments.length === 1) return data.get(node)[key]; data.get(node)[key] = value; return this; }};
}
$.ajax = options => {
    const req = {options, done(fn) {this.success = fn; return this;}, fail(fn) {this.failure = fn; return this;}, always(fn) {this.final = fn; return this;},
        resolve(value) {this.success(value); if (this.final) this.final();}, reject() {this.failure(); if (this.final) this.final();}};
    requests.push(req); return req;
};
const context = {document: {hidden: false, getElementById: id => nodes[id]}, $, _jaEmailConversationTimer: 41,
    setInterval(fn) {tick = fn; return 42;}, clearInterval(id) {cleared.push(id);}};
context.window = context;
vm.createContext(context);
vm.runInContext(code, context);
assert(cleared.includes(41), 'Replacing profiles must clear the old poll timer');
requests[0].resolve({unread: 1});
assert.equal(badge.textContent, '1 new');
assert.equal(badge.style.display, 'inline-flex');
pane.style.display = 'block'; tick();
assert.equal(requests[1].options.url, '/email/3280', 'Open conversation must fetch actual replies, not just badge counts');
assert.equal(requests[1].options.cache, false);
const response = {messages: [
    {direction: 'outbound', user: {name: 'Yogesh'}, subject: 'Test template subjest 2', body: 'test', received_at: '2026-10-02T16:00:00Z'},
    {direction: 'inbound', subject: 'Re: Test template subjest 2', body: 'just for test & <script>alert(1)</script>\nOn Friday HR wrote:\n> Signature', display_body: 'just for test & <script>alert(1)</script>', quoted_body: 'On Friday HR wrote:\nSignature <img src=x onerror=alert(1)>', received_at: '2026-10-02T17:56:00Z'}
]};
requests[1].resolve(response);
assert(box.innerHTML.includes('Candidate · Re: Test template subjest 2'), 'Render candidate reply on profile');
assert(box.innerHTML.includes('just for test &amp; &lt;script&gt;'), 'Reply text must be escaped safely');
assert(box.innerHTML.includes('Yogesh · Test template subjest 2'), 'Show actual sending team member');
assert.equal(badge.style.display, 'none', 'Viewing conversation clears the unread indicator');
assert(box.innerHTML.includes('<details') && box.innerHTML.includes('Show quoted email'), 'Quoted history should be collapsible');
assert(!box.innerHTML.includes('<details open') && !box.innerHTML.includes('<img'), 'Keep quotes collapsed and safely escape their content');
const old = box.innerHTML;
tick(); requests[2].reject();
assert.equal(box.innerHTML, old, 'A failed refresh must preserve existing conversation');
box.innerHTML = 'User opened the quoted email';
tick(); requests[3].resolve(response);
assert.equal(box.innerHTML, 'User opened the quoted email', 'An unchanged refresh must preserve expanded quote state');
context.document.hidden = true; tick();
assert.equal(requests.length, 4, 'Hidden page should not keep issuing requests');
delete nodes['ja-email-conversation-3280']; tick();
assert(cleared.includes(42), 'Closing the profile must stop polling');
console.log('PASS: candidate reply rendering, team member names, safe text, live refresh, unread badges, and timer cleanup');
