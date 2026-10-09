const fs = require('fs');
const vm = require('vm');
const assert = require('assert');

const storage = new Map();
let storageBlocked = false;
const localStorage = {
    getItem(key) { if (storageBlocked) throw new Error('Storage unavailable'); return storage.get(key) ?? null; },
    setItem(key, value) { storage.set(key, value); }
};
function stage(id) {
    return {id, count:id * 2, applicants:[{name:'Candidate ' + id}], getAttribute:()=>String(id)};
}
function list(jobId, ids) {
    return {jobId, children:ids.map(stage), moves:0,
        getAttribute:()=>String(jobId), querySelectorAll(){return this.children.slice();},
        appendChild(node){this.children=this.children.filter(child=>child!==node);this.children.push(node);this.moves++;}};
}
function ids(list) { return list.children.map(stage=>stage.id); }
const totalPopup = list(42, [1,2,3,4]);
const statusPopup = list(42, [1,2,3,4]);
const otherJob = list(43, [5,6,7]);
const withoutSavedOrder = list(44, [8,9]);
storage.set('ja-tab-order-43', JSON.stringify([7,5,6]));
const events = {};
const overview = {localStorage, document:{querySelectorAll:()=>[totalPopup,statusPopup,otherJob,withoutSavedOrder]},
    addEventListener(type, fn){events[type]=fn;}};
overview.window=overview;
vm.createContext(overview);
const source = fs.readFileSync('resources/views/admin/ats-overview/status-order-script.blade.php','utf8')
    .replace(/^<script>\s*/, '').replace(/<\/script>\s*$/, '');
vm.runInContext(source,overview);
assert.deepEqual(ids(totalPopup),[1,2,3,4],'No saved order keeps the database order');
assert.deepEqual(ids(otherJob),[7,5,6],'Apply the order separately for each job on initial load');
const originalStages = totalPopup.children.slice();

// Run the actual Job Applications persistence and restore functions against the same storage.
const jobView = fs.readFileSync('resources/views/admin/job-applications/index.blade.php','utf8');
const dragStart = jobView.indexOf('    (function () {', jobView.indexOf('// ── Draggable stage tab reordering'));
const dragEnd = jobView.indexOf('    // ── Build/rebuild pipeline stage tab buttons', dragStart);
assert(dragStart > 0 && dragEnd > dragStart,'Find the real stage-order code');
const tabWrap = list(42,[1,2,3,4]);
const tabRow = {querySelectorAll:()=>tabWrap.children.slice(), addEventListener(){}};
const jobPage = {localStorage, jaCurrentJobId:42, jaStages:[1,2,3,4].map(id=>({id})),
    document:{readyState:'complete',getElementById:id=>id==='ja-stage-tabs-row'?tabRow:tabWrap}};
jobPage.window=jobPage;
vm.createContext(jobPage);
vm.runInContext(jobView.slice(dragStart,dragEnd),jobPage);

tabWrap.children = [3,1,4,2].map(id=>tabWrap.children.find(tab=>tab.id===id));
jobPage.jaPersistOrder();
events.storage({key:'ja-tab-order-42'});
assert.deepEqual(ids(totalPopup),ids(tabWrap),'Total applicants popup follows the real saved tab order');
assert.deepEqual(ids(statusPopup),ids(tabWrap),'View Status popup follows the same saved tab order');
assert.deepEqual(ids(otherJob),[7,5,6],'Reordering a job does not change another job');
assert.deepEqual(ids(withoutSavedOrder),[8,9]);
originalStages.forEach(node=>assert(totalPopup.children.includes(node),'Move the original rows with counts and applicant lists attached'));
assert.equal(totalPopup.children[0].count,6);
assert.equal(totalPopup.children[0].applicants[0].name,'Candidate 3');

tabWrap.children = [2,4,1,3].map(id=>tabWrap.children.find(tab=>tab.id===id));
jobPage.jaPersistOrder();
events.focus();
assert.deepEqual(ids(totalPopup),ids(tabWrap),'Returning to Overview also applies the reverse reorder');
const moveCount = totalPopup.moves;
overview.atsApplyStoredStageOrders({contains:node=>node===totalPopup});
assert.equal(totalPopup.moves,moveCount,'Opening an unchanged tooltip does not keep moving its rows');

storage.delete('ja-tab-order-42');events.storage({key:'ja-tab-order-42'});
assert.deepEqual(ids(totalPopup),[1,2,3,4],'Removing a saved order restores the original pipeline order');
for (const invalid of ['{broken', '{"stage":3}', JSON.stringify([3,99,1]), JSON.stringify([1,1,2])]) {
    storage.set('ja-tab-order-42',invalid);events.pageshow();
    assert.deepEqual(ids(totalPopup),[1,2,3,4],'Malformed or stale storage must not lose or duplicate statuses');
}
storage.set('ja-tab-order-42',JSON.stringify([4,1,2]));
tabWrap.children=[1,2,3,4].map(stage);jobPage.jaApplyStoredOrder();
events.storage({key:'ja-tab-order-42'});
assert.deepEqual(ids(totalPopup),ids(tabWrap),'Newly added stages follow exactly the same restore behavior as Job Applications');
storageBlocked=true;events.focus();
assert.deepEqual(ids(totalPopup),[1,2,3,4],'Blocked storage leaves all statuses available in their default order');
storageBlocked=false;storage.clear();events.storage({key:null});
assert.deepEqual(ids(otherJob),[5,6,7],'Clearing storage restores every job order');
console.log('PASS: Job Applications and both overview popups share per-job ordering, reverse moves, tab synchronization, intact applicant data and safe fallbacks');
