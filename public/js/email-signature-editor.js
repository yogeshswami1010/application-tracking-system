(function () {
    'use strict';
    const frame = document.getElementById('signature-visual-editor');
    if (!frame) return;
    const config = JSON.parse(document.getElementById('signature-editor-config').textContent);
    const source = document.getElementById('email_signature_html');
    const status = document.getElementById('signature-editor-status');
    let doc, selection, selectedImage, pending = 0;
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
                command('insertHTML', html);
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
    document.getElementById('signature-resize-image').addEventListener('click', function () {
        if (!selectedImage) { status.textContent = 'Click an image in the signature first.'; return; }
        const width = Number(prompt('Image width in pixels (16–1000)', selectedImage.width || 150));
        if (width >= 16 && width <= 1000) {
            selectedImage.width = Math.round(width); selectedImage.removeAttribute('height'); selectedImage.style.width = width + 'px'; selectedImage.style.height = 'auto'; sync();
        }
    });
    document.getElementById('signature-apply-html').addEventListener('click', function () { doc.body.innerHTML = source.value; selection = null; sync(); });
    document.getElementById('save-form').addEventListener('click', function (event) {
        if (pending) { event.preventDefault(); event.stopImmediatePropagation(); status.textContent = 'Wait for image uploads to finish.'; }
    }, true);
})();
