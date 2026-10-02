(function () {
    'use strict';
    const frame = document.getElementById('signature-visual-editor');
    if (!frame) return;
    const config = JSON.parse(document.getElementById('signature-editor-config').textContent);
    const source = document.getElementById('email_signature_html');
    const status = document.getElementById('signature-editor-status');
    let doc, selection, selectedImage, pending = 0;
    window.syncEmailSignature = function () {
        if (!doc || !doc.body || pending) {
            status.textContent = 'Please wait for the signature editor and image uploads to finish.';
            return false;
        }
        sync();
        const payload = document.getElementById('email_signature_payload');
        payload.value = btoa(Array.from(new TextEncoder().encode(source.value), byte => String.fromCharCode(byte)).join(''));
        payload.disabled = false;
        return true;
    };
    function sync() {
        source.value = doc.body.textContent.trim() || doc.body.querySelector('img') ? doc.body.innerHTML : '';
    }
    function remember() {
        const current = doc.getSelection();
        if (current.rangeCount) selection = current.getRangeAt(0).cloneRange();
    }
    function restore() {
        frame.contentWindow.focus();
        if (selection && doc.body.contains(selection.commonAncestorContainer)) {
            const current = doc.getSelection(); current.removeAllRanges(); current.addRange(selection);
        }
    }
    function command(name, value) {
        restore(); doc.execCommand('styleWithCSS', false, true); doc.execCommand(name, false, value); remember(); sync();
    }
    function insertSignatureHtml(html) {
        restore();
        const template = doc.createElement('template');
        template.innerHTML = html;
        template.content.querySelectorAll('script,iframe,object,embed,svg,math,form,meta,link,base').forEach(node => node.remove());
        template.content.querySelectorAll('*').forEach(node => {
            Array.from(node.attributes).forEach(attr => {
                if (/^on/i.test(attr.name)) node.removeAttribute(attr.name);
            });
        });
        const current = doc.getSelection();
        let range = current.rangeCount ? current.getRangeAt(0) : null;
        if (!range || !doc.body.contains(range.commonAncestorContainer)) {
            range = doc.createRange(); range.selectNodeContents(doc.body); range.collapse(false);
        }
        // Native insertHTML can flatten pasted signature tables and merge paragraphs.
        // Insert the original DOM fragment so rows, cells, and inline image sizes survive.
        range.deleteContents();
        const last = template.content.lastChild;
        range.insertNode(template.content);
        if (last) { range.setStartAfter(last); range.collapse(true); }
        current.removeAllRanges(); current.addRange(range);
        remember(); sync();
    }
    async function upload(files) {
        for (const file of files) {
            if (!['image/png', 'image/jpeg'].includes(file.type) || file.size > 2097152) {
                status.textContent = 'Choose PNG or JPG images up to 2 MB each.'; continue;
            }
            pending++;
            document.getElementById('save-form').disabled = true;
            status.textContent = 'Uploading signature image…';
            try {
                const data = new FormData(); data.append('image', file);
                const response = await fetch(config.uploadUrl, {method:'POST', credentials:'same-origin', headers:{'X-CSRF-TOKEN':config.token, 'Accept':'application/json'}, body:data});
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Image upload failed.');
                restore();
                const img = doc.createElement('img'); img.src = result.url; img.alt = file.name;
                img.width = 150; img.style.height = 'auto'; img.style.maxWidth = '100%';
                const current = doc.getSelection();
                if (current.rangeCount && doc.body.contains(current.getRangeAt(0).commonAncestorContainer)) {
                    const range = current.getRangeAt(0); range.deleteContents(); range.insertNode(img); range.setStartAfter(img); range.collapse(true);
                    current.removeAllRanges(); current.addRange(range);
                } else doc.body.appendChild(img);
                selectedImage = img; remember(); sync(); status.textContent = 'Image added. Click Image size to adjust its width, then save your profile.';
            } catch (error) { status.textContent = error.message; }
            finally { pending--; document.getElementById('save-form').disabled = pending > 0; }
        }
    }
    frame.addEventListener('load', function () {
        doc = frame.contentDocument;
        doc.body.contentEditable = 'true';
        doc.body.setAttribute('aria-label', 'Email signature');
        doc.body.style.cssText = 'font-family:Arial,sans-serif;font-size:14px;padding:12px;margin:0;min-height:290px;color:#222;background:white;';
        doc.body.innerHTML = config.initialHtml || '';
        doc.addEventListener('selectionchange', remember);
        doc.body.addEventListener('input', sync);
        doc.body.addEventListener('click', function (event) { selectedImage = event.target.closest('img'); if (event.target.closest('a')) event.preventDefault(); });
        doc.body.addEventListener('paste', function (event) {
            const clipboard = event.clipboardData;
            if (!clipboard) return;
            const html = clipboard.getData('text/html');
            event.preventDefault();
            if (html) {
                // Pasted markup remains inside the script-disabled, CSP-restricted iframe.
                insertSignatureHtml(html);
                status.textContent = 'Signature pasted. Check image sizes and save your profile. Images must use public web URLs; local or embedded images should be uploaded with Add images.';
            } else if (clipboard.files.length) upload(Array.from(clipboard.files));
            else command('insertText', clipboard.getData('text/plain'));
        });
        doc.body.addEventListener('dragover', event => event.preventDefault());
        doc.body.addEventListener('drop', function (event) { event.preventDefault(); if (event.dataTransfer.files.length) upload(Array.from(event.dataTransfer.files)); });
    }, {once:true});
    frame.srcdoc = '<!doctype html><html><head><meta http-equiv="Content-Security-Policy" content="default-src &apos;none&apos;; img-src https: http:; style-src &apos;unsafe-inline&apos;"></head><body></body></html>';
    document.querySelectorAll('#signature-toolbar [data-command]').forEach(button => {
        button.addEventListener('mousedown', event => event.preventDefault());
        button.addEventListener('click', () => command(button.dataset.command));
    });
    document.getElementById('signature-font').addEventListener('change', event => command('fontName', event.target.value));
    document.getElementById('signature-size').addEventListener('change', event => command('fontSize', event.target.value));
    document.getElementById('signature-color').addEventListener('change', event => command('foreColor', event.target.value));
    document.getElementById('signature-link').addEventListener('click', function () {
        const url = prompt('Link URL (https://, mailto:, or tel:)');
        if (url && /^(https?:\/\/|mailto:|tel:)/i.test(url)) command('createLink', url);
    });
    document.getElementById('signature-add-image').addEventListener('click', () => document.getElementById('signature-inline-images').click());
    document.getElementById('signature-inline-images').addEventListener('change', function () { upload(Array.from(this.files)); this.value = ''; });
    document.getElementById('signature-columns').addEventListener('click', function () {
        const logo = selectedImage && doc.body.contains(selectedImage) ? selectedImage : doc.body.querySelector('img');
        if (!logo) { status.textContent = 'Add your logo first, then choose Logo left / details right.'; return; }
        if (logo.closest('table')) {
            const table = logo.closest('table');
            table.querySelectorAll('td,th').forEach(cell => { cell.setAttribute('valign', 'top'); cell.style.verticalAlign = 'top'; });
            sync(); status.textContent = 'Table content aligned to the top. Save your profile to apply.'; return;
        }
        // Split the existing content after the logo, retaining links and inline formatting.
        // A large later image or disclaimer text marks the full-width footer.
        let footer = null;
        const walker = doc.createTreeWalker(doc.body, 5);
        let passedLogo = false, node;
        while ((node = walker.nextNode())) {
            if (node === logo) { passedLogo = true; continue; }
            if (!passedLogo) continue;
            if (node.nodeType === 1 && node.tagName === 'IMG' && (node.width >= 80 || node.naturalWidth >= 300)) { footer = node; break; }
            if (node.nodeType === 3 && /please consider the environment|the information in this electronic communication/i.test(node.textContent)) { footer = node; break; }
        }
        // Include the footer's wrapper only when it contains no contact details before it.
        if (footer) {
            while (footer.parentNode !== doc.body && footer.parentNode.firstChild === footer) footer = footer.parentNode;
        }
        const detailsRange = doc.createRange();
        detailsRange.setStartAfter(logo);
        if (footer) detailsRange.setEndBefore(footer);
        else detailsRange.setEnd(doc.body, doc.body.childNodes.length);
        if (!detailsRange.toString().trim()) { status.textContent = 'Add your name and contact details after the logo first.'; return; }
        const before = doc.body.innerHTML;
        const width = Math.min(250, Math.max(80, logo.width || 170));
        const details = detailsRange.extractContents();
        const table = doc.createElement('table');
        table.setAttribute('role', 'presentation'); table.setAttribute('cellpadding', '0'); table.setAttribute('cellspacing', '0'); table.setAttribute('border', '0');
        table.style.cssText = 'border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;';
        const row = table.insertRow(); const left = row.insertCell(); const right = row.insertCell();
        left.setAttribute('valign', 'top'); right.setAttribute('valign', 'top');
        left.style.cssText = 'vertical-align:top;padding:0 14px 0 0;width:' + width + 'px;';
        right.style.cssText = 'vertical-align:top;padding:0;';
        logo.replaceWith(table); left.appendChild(logo); right.appendChild(details);
        logo.width = width; logo.style.cssText = 'display:block;width:' + width + 'px;height:auto;border:0;';
        // Avoid placing a table inside an inline/paragraph wrapper from a pasted signature.
        let wrapper = table.parentNode;
        while (wrapper !== doc.body && !wrapper.textContent.trim() && wrapper.querySelectorAll('img').length === 1) {
            const parent = wrapper.parentNode; parent.insertBefore(table, wrapper); wrapper.remove(); wrapper = parent;
        }
        selection = null; sync();
        const undo = document.getElementById('signature-layout-undo');
        undo.hidden = false;
        undo.onclick = function () { doc.body.innerHTML = before; selectedImage = null; selection = null; sync(); undo.hidden = true; };
        status.textContent = 'Logo and contact details now use two top-aligned columns. The disclaimer stays below. Save your profile to apply.';
    });
    document.getElementById('signature-resize-image').addEventListener('click', function () {
        if (!selectedImage) { status.textContent = 'Click an image in the signature first.'; return; }
        const width = Number(prompt('Image width in pixels (16–1000)', selectedImage.width || 150));
        if (width >= 16 && width <= 1000) {
            selectedImage.width = Math.round(width); selectedImage.removeAttribute('height'); selectedImage.style.width = width + 'px'; selectedImage.style.height = 'auto'; sync();
        }
    });
    document.getElementById('save-form').addEventListener('click', function (event) {
        if (!window.syncEmailSignature()) { event.preventDefault(); event.stopImmediatePropagation(); status.textContent = 'Wait for image uploads to finish.'; }
    }, true);
})();
