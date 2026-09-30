const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const template = fs.readFileSync('resources/views/admin/job-applications/show.blade.php', 'utf8');
const begin = template.indexOf("var callRoot = Array.from(");
const end = template.indexOf('})();', begin);
const source = template.slice(begin, end).replace('@json((string) $application->id)', "'1'").replace(/@json\(asset\('js\/candidate-calls.js'\).*;/, "'/candidate-calls.js?v=test';");
(async () => {
    const listeners = {};
    const classes = new Set(['hidden']);
    const start = {addEventListener: (name, fn) => listeners.start = fn, removeEventListener() {}, disabled: false};
    const close = {addEventListener: (name, fn) => listeners.close = fn, removeEventListener() {}};
    const status = {};
    const parent = {appendChild(el) {el.parentNode = this;}};
    const root = {style: {}, dataset: {applicationId: '1'}, parentNode: parent, isConnected: true,
        classList: {add: c => classes.add(c), remove: c => classes.delete(c)},
        querySelector: s => ({'#call-start': start, '#call-close': close, '#call-status': status}[s])};
    let loads = 0;
    const window = {};
    const document = {querySelectorAll: () => [{dataset:{applicationId:'2'}}, root], getElementById: () => root, body: {appendChild(el) {el.parentNode = this;}},
        createElement: () => ({}), head: {appendChild(script) {loads++; script.onload();}}};
    vm.runInNewContext('(function(){' + source + '})();', {window, document});
    window.jaOpenCallModal(1);
    assert.equal(loads, 0);
    assert.ok(classes.has('flex')); assert.equal(root.style.display, 'flex');
    listeners.close();
    assert.ok(classes.has('hidden'));
    await listeners.start({preventDefault(){}, stopImmediatePropagation(){}});
    assert.equal(loads, 1);
    assert.match(status.textContent, /outdated/);
    assert.equal(start.disabled, false);
    assert.equal(window._jaCandidateCallAssetsPromise, null);
    console.log('PASS: popup opens and closes without assets; outdated script fails once without recursive loading');
})().catch(error => {console.error(error); process.exitCode = 1;});