const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const view = fs.readFileSync('resources/views/admin/job-applications/index.blade.php', 'utf8');
const code = view.slice(view.indexOf('    function jaMoveOne('), view.indexOf("    document.addEventListener('click'", view.indexOf('    function jaMoveOne(')))
    .replace(/\{\{ route\([^]*?\) \}\}/g, '/stage-update')
    .replaceAll('{{ csrf_token() }}', 'test-token');
function menu(id, appId, currentId, stages) {
    const attributes = {'data-app-id': String(appId), 'data-current-status-id': String(currentId), 'data-stages': JSON.stringify(stages)};
    return {id, style: {display: 'none'}, innerHTML: '', getAttribute: key => attributes[key]};
}
const first = menu('ja-move-drop-10', 10, 101, [
    {id: 101, label: 'Applied'}, {id: 103, label: 'Interview', color: '#D97706'},
]);
const second = menu('ja-move-drop-20', 20, 201, [
    {id: 201, label: 'Applied'}, {id: 205, label: 'Client & Technical Review', color: '#7C3AED'},
]);
const empty = menu('ja-move-drop-30', 30, 0, []);
const menus = [first, second, empty];
const requests = [];
let draws = 0, counts = 0;
const context = {
    // These deliberately differ from both jobs; row menus must never use them.
    jaStages: [{id: 2, label: 'Wrong global stage', slug: 'rejected'}],
    jaSelectedIds: new Set([10, 20]),
    table: {draw(reset) { assert.equal(reset, false); draws++; }},
    jaLoadTabCounts() { counts++; }, jaRenderBulkBar() {},
    $: {easyAjax: request => requests.push(request)},
    document: {querySelectorAll: () => menus, getElementById: id => menus.find(menu => menu.id === id)},
};
vm.createContext(context);
vm.runInContext(code, context);
const button = {getBoundingClientRect: () => ({bottom: 100, right: 300})};
context.jaToggleDrop(first.id, button);
assert(first.innerHTML.includes('Interview'));
assert(!first.innerHTML.includes('Wrong global stage') && !first.innerHTML.includes('Applied'));
let action = first.innerHTML.match(/onclick="([^"]+)"/)[1];
vm.runInContext(action, context);
assert.equal(requests[0].data.status_id, 103);
assert.deepEqual(Array.from(requests[0].data.ids), [10]);
requests[0].success({status: 'success'});
assert.equal(draws, 1); assert.equal(counts, 1);
assert(!context.jaSelectedIds.has(10));
assert.equal(first.style.display, 'none');
context.jaToggleDrop(second.id, button);
assert(second.innerHTML.includes('Client &amp; Technical Review'), 'Render custom labels safely');
assert(!second.innerHTML.includes('Interview') && !second.innerHTML.includes('Wrong global stage'));
action = second.innerHTML.match(/onclick="([^"]+)"/)[1];
vm.runInContext(action, context);
assert.equal(requests[1].data.status_id, 205);
assert.deepEqual(Array.from(requests[1].data.ids), [20]);
context.jaToggleDrop(empty.id, button);
assert(empty.innerHTML.includes('No stages available'), 'An empty job pipeline must not fall back to global stages');
console.log('PASS: each row uses only its job destinations, custom labels are escaped, and selection posts the job status ID');
