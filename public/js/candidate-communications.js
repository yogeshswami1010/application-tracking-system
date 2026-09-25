(() => {
    'use strict';
    const root = document.getElementById('candidate-communications');
    if (!root) return;
    const el = id => document.getElementById('cc-' + id);
    let channel = 'email', recipients = [], templates = [], sending = false, opening = false;
    const drafts = {email: '', sms: ''};
    const selection = () => {
        if (typeof window.ccSelection === 'function') return window.ccSelection();
        return Array.from(document.querySelectorAll('.cc-select:checked')).map(box => ({type: box.dataset.type, id: Number(box.value)}));
    };
    const feedback = message => { el('feedback').textContent = message; };
    window.ccRefreshSelection = () => {
        const count = selection().length;
        el('count').textContent = count;
        root.querySelectorAll('[data-cc-open]').forEach(button => { button.disabled = count === 0 || count > 100; });
        document.querySelectorAll('.cc-all').forEach(box => {
            const rows = Array.from((box.closest('table') || document).querySelectorAll('.cc-select'));
            box.checked = rows.length > 0 && rows.every(row => row.checked);
            box.indeterminate = rows.some(row => row.checked) && !box.checked;
        });
    };
    async function request(url, data) {
        const response = await fetch(url, {method: data ? 'POST' : 'GET', headers: {
            'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': root.dataset.token,
        }, ...(data ? {body: JSON.stringify(data)} : {})});
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'Request failed. Refresh the page and check your connection.');
        return result;
    }
    async function loadTemplates() {
        templates = (await request(root.dataset.templatesUrl)).templates;
        // Keep earlier browser-only AI Search templates available for saving to the account.
        try {
            const legacy = JSON.parse(localStorage.getItem('ai_email_templates') || '[]');
            if (Array.isArray(legacy)) legacy.forEach((item, index) => {
                if (item && typeof item.name === 'string' && typeof item.subject === 'string' && typeof item.message === 'string' && !templates.some(saved => saved.name === item.name)) {
                    templates.push({...item, id: 'browser-' + index});
                }
            });
        } catch (_) { /* Storage may be unavailable. Server templates still work. */ }
        el('template').replaceChildren(new Option('Choose a template', ''));
        templates.forEach(template => el('template').add(new Option(template.name + (String(template.id).startsWith('browser-') ? ' (browser template)' : ''), template.id)));
    }
    window.ccOpen = async (nextChannel, selectedRecipients = null) => {
        if (sending || opening) return;
        const selected = selectedRecipients ?? selection();
        if (!selected.length || selected.length > 100) return;
        opening = true;
        drafts[channel] = el('message').value;
        channel = nextChannel;
        el('message').value = drafts[channel];
        recipients = [];
        el('title').textContent = selectedRecipients ? (channel === 'email' ? 'Send email' : 'Send SMS') : (channel === 'email' ? 'Bulk email' : 'Bulk SMS');
        el('send').textContent = selectedRecipients ? 'Send message' : 'Send to selected candidates';
        el('email-fields').hidden = channel !== 'email';
        el('template-panel').hidden = channel !== 'email';
        el('subject').required = channel === 'email';
        el('message').maxLength = channel === 'sms' ? 1600 : 10000;
        el('summary').textContent = 'Checking selected candidates…';
        el('recipients').replaceChildren();
        feedback('');
        el('send').disabled = true;
        el('dialog').showModal();
        try {
            const preview = await request(root.dataset.previewUrl, {channel, recipients: selected});
            recipients = preview.recipients.filter(recipient => !recipient.reason);
            preview.recipients.forEach(recipient => {
                const item = document.createElement('li');
                item.textContent = recipient.name + ' — ' + (recipient.reason || recipient.address);
                el('recipients').append(item);
            });
            el('summary').textContent = recipients.length + ' recipients ready; ' + (selected.length - recipients.length) + ' skipped (see recipient details).';
            el('send').disabled = recipients.length === 0;
            if (channel === 'email') await loadTemplates();
        } catch (error) { feedback(error.message); }
        finally { opening = false; }
    };
    root.querySelectorAll('[data-cc-open]').forEach(button => button.addEventListener('click', () => window.ccOpen(button.dataset.ccOpen)));
    document.addEventListener('change', event => {
        if (event.target.matches('.cc-all')) {
            (event.target.closest('table') || document).querySelectorAll('.cc-select').forEach(box => { box.checked = event.target.checked; });
        }
        if (event.target.matches('.cc-all,.cc-select,.ai-result-select')) window.ccRefreshSelection();
    });
    if (window.jQuery) window.jQuery(document).on('draw.dt ajaxComplete', window.ccRefreshSelection);
    el('template').addEventListener('change', () => {
        const template = templates.find(item => String(item.id) === el('template').value);
        if (!template) return;
        el('subject').value = template.subject;
        el('message').value = template.message;
        el('template-name').value = template.name;
    });
    el('save-template').addEventListener('click', async () => {
        const data = {name: el('template-name').value.trim(), subject: el('subject').value.trim(), message: el('message').value.trim()};
        if (!data.name || !data.subject || !data.message) return feedback('Enter a template name, subject, and message.');
        el('save-template').disabled = true;
        try {
            await request(root.dataset.templatesUrl, data);
            await loadTemplates();
            feedback('Email template saved to your account.');
        } catch (error) { feedback(error.message); }
        finally { el('save-template').disabled = false; }
    });
    ['close', 'close-top'].forEach(id => el(id).addEventListener('click', () => { if (!sending) el('dialog').close(); }));
    el('dialog').addEventListener('cancel', event => { if (sending) event.preventDefault(); });
    el('form').addEventListener('submit', async event => {
        event.preventDefault();
        if (sending || !recipients.length) return;
        const message = el('message').value.trim(), subject = el('subject').value.trim();
        if (!message || (channel === 'email' && !subject)) return feedback('Enter the subject and message before sending.');
        sending = true;
        const controls = Array.from(el('form').querySelectorAll('button,input,select,textarea'));
        controls.forEach(control => { control.disabled = true; });
        let sent = 0, failed = 0, skipped = 0;
        const problems = [];
        // One recipient per request avoids a bulk HTTP timeout after partial delivery.
        for (let index = 0; index < recipients.length; index++) {
            const recipient = recipients[index];
            feedback('Sending ' + (index + 1) + ' of ' + recipients.length + '… Keep this window open.');
            try {
                const response = await request(root.dataset.sendUrl, {channel, subject, message, source: root.dataset.source, recipients: [{type: recipient.type, id: recipient.id}]});
                const result = response.results[0];
                if (result.status === 'sent') sent++;
                else {
                    if (result.status === 'skipped') skipped++; else failed++;
                    problems.push(recipient.name + ': ' + result.reason);
                }
            } catch (error) {
                problems.push(recipient.name + ': delivery is unconfirmed. ' + error.message + ' Check before retrying. ' + (recipients.length - index - 1) + ' remaining recipients were not attempted.');
                break;
            }
        }
        feedback(sent + ' sent, ' + failed + ' failed, ' + skipped + ' skipped.\n' + problems.join('\n'));
        sending = false;
        recipients = [];
        controls.forEach(control => { control.disabled = false; });
        el('send').disabled = true;
    });
    window.addEventListener('beforeunload', event => { if (sending) { event.preventDefault(); event.returnValue = ''; } });
    window.ccRefreshSelection();
})();
