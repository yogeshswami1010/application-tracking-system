const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const view = fs.readFileSync('resources/views/admin/job-applications/show.blade.php', 'utf8');
const code = view.slice(view.indexOf('function jaToggleProfileStage('), view.indexOf('/* ── Skills ── */'))
    .replace(/\{\{ route\([^]*?\) \}\}/g, '/applications/:id/stages')
    .replaceAll('{{ csrf_token() }}', 'test-token');
const form = {style: {}, isConnected: true, reportValidity: () => true, reset() { name.value = ''; }};
const name = {value: ' Reference check ', focus() { this.focused = true; }};
const color = {value: '#7C3AED'};
const button = {disabled: false, textContent: 'Save stage'};
const message = {style: {}, textContent: ''};
const add = {value: '__add_stage__'};
const select = {value: '__add_stage__', options: [{value: ''}, {value: '8', textContent: 'Applied', 'data-color': '#2563EB', disabled: true}, {value: '11', textContent: 'Interview', 'data-color': '#D97706'}, add],
    querySelector: () => add,
    insertBefore(option, target) { this.options.splice(this.options.indexOf(target), 0, option); }};
const nodes = {
    'ja-stage-form-3280': form, 'ja-stage-name-3280': name, 'ja-stage-color-3280': color,
    'ja-stage-save-3280': button, 'ja-stage-message-3280': message, 'stage-mover-select-3280': select,
};
const requests = [];
const moves = [];
const pill = {style: {}};
const badge = {style: {}, appendChild(node) { this.label = node.textContent; }};
const scheduleAction = {style: {display: 'none'}};
nodes['schedule-interview-action-3280'] = scheduleAction;
const context = {$: {ajax: request => requests.push(request), easyAjax: request => moves.push(request)},
    document: {getElementById: id => nodes[id],
        querySelectorAll: selector => selector === '.ja-pill-stage' ? [pill] : [badge],
        createTextNode: textContent => ({textContent}),
        createElement: () => ({setAttribute(key, value) { this[key] = value; }})}};
vm.createContext(context);
vm.runInContext(code, context);
context.jaMoveFromDetail(3280, '__add_stage__', 'Add new stage', 8);
assert.equal(moves.length, 0, 'Creating a stage must not try to move to the sentinel value');
assert.equal(select.value, '');
assert.equal(form.style.display, 'block');
assert(name.focused);
context.jaSaveProfileStage(3280);
context.jaSaveProfileStage(3280);
assert.equal(requests.length, 1, 'Double-clicks must not create duplicate requests');
assert.equal(requests[0].data.status_name, 'Reference check');
assert.equal(requests[0].data.status_color, '#7C3AED');
assert.equal(requests[0].data.job_id, undefined, 'The server determines the current job');
requests[0].success({status: 'success', stage: {id: 20, label: 'Reference Check', color: '#7C3AED'}});
requests[0].complete();
assert.equal(select.options[select.options.length - 1], add, 'Keep Add new stage at the end');
assert.equal(select.options[select.options.length - 2].textContent, 'Reference Check');
assert.equal(select.options[select.options.length - 2]['data-color'], '#7C3AED');
assert.equal(form.style.display, 'none');
assert.equal(moves.length, 0, 'Saving a stage must leave the current candidate assignment intact');
assert(!button.disabled);
context.jaToggleProfileStage(3280, true);
name.value = 'Reference Check';
context.jaSaveProfileStage(3280);
requests[1].error({responseJSON: {errors: {status_name: ['This stage already exists for this job.']}}});
requests[1].complete();
assert.equal(message.textContent, 'This stage already exists for this job.');
assert.equal(form.style.display, 'block', 'Keep invalid input editable');
assert(!button.disabled);
context.jaToggleProfileStage(3280, false);
assert.equal(form.style.display, 'none');
context.jaToggleProfileStage(3280, true);
context.jaSaveProfileStage(3280);
form.isConnected = false;
requests[2].success({status: 'success', stage: {id: 21, label: 'Stale stage', color: '#2563EB'}});
requests[2].complete();
assert(!select.options.some(option => option.value === '21'), 'Ignore responses after the profile is closed');
form.isConnected = true;
context.jaMoveFromDetail(3280, '20', 'Reference Check', 8);
assert.equal(moves.length, 1, 'The saved stage must work with the existing stage mover');
assert.equal(moves[0].data.status_id, '20');
const stageIds = select.options.map(option => option.value);
let draws = 0;
let counts = 0;
context.table = {draw(reset) { assert.equal(reset, false); draws++; }};
context.jaLoadTabCounts = () => counts++;
function finishMove(id, label, request) {
    select.value = id;
    select.selectedIndex = select.options.findIndex(option => option.value === id);
    select.options.forEach(option => {
        option.getAttribute = key => option[key];
        option.remove = () => select.options.splice(select.options.indexOf(option), 1);
    });
    request.success({status: 'success'});
    assert.deepEqual(select.options.map(option => option.value), stageIds, 'Moving must retain every pipeline stage in its original order');
    assert.equal(select.value, '', 'Reset the mover to its placeholder');
    assert.equal(pill.textContent, label);
    assert.equal(badge.label, label);
    assert.equal(select.options.find(option => option.value === id).disabled, true, 'Only the current stage is unavailable as a destination');
    select.options.filter(option => option.value && option.value !== id && option.value !== '__add_stage__')
        .forEach(option => assert(!option.disabled, 'Previous stages must become available again'));
}
finishMove('20', 'Reference Check', moves[0]);
context.jaMoveFromDetail(3280, '11', 'Interview', 8);
finishMove('11', 'Interview', moves[1]);
assert.equal(scheduleAction.style.display, '');
context.jaMoveFromDetail(3280, '8', 'Applied', 8);
finishMove('8', 'Applied', moves[2]);
assert.equal(scheduleAction.style.display, 'none');
assert.equal(draws, 3);
assert.equal(counts, 3);
assert.equal(select.options[select.options.length - 1], add, 'Adding stages must remain available after repeated moves');
console.log('PASS: profile stage form, duplicate submission guard, validation feedback, cancellation, and stage selection');
console.log('PASS: repeated stage moves and moving back preserve all options and update badges without a refresh');
