<div class="ja-review-editor-wrap">
    <div class="ja-review-format" role="toolbar" aria-label="Message formatting">
        <button type="button" data-review-command="bold" data-editor="{{ $editorId }}" title="Bold" aria-label="Bold"><b>B</b></button>
        <button type="button" data-review-command="italic" data-editor="{{ $editorId }}" title="Italic" aria-label="Italic"><i>I</i></button>
        <button type="button" data-review-command="underline" data-editor="{{ $editorId }}" title="Underline" aria-label="Underline"><u>U</u></button>
        <button type="button" data-review-command="insertUnorderedList" data-editor="{{ $editorId }}" title="Bullet list" aria-label="Bullet list">• List</button>
        <button type="button" data-review-command="insertOrderedList" data-editor="{{ $editorId }}" title="Numbered list" aria-label="Numbered list">1. List</button>
        <select data-review-command="fontName" data-editor="{{ $editorId }}" aria-label="Font"><option value="Arial">Arial</option><option value="Georgia">Georgia</option><option value="Tahoma">Tahoma</option><option value="Verdana">Verdana</option><option value="Times New Roman">Times New Roman</option></select>
        <select data-review-command="fontSize" data-editor="{{ $editorId }}" aria-label="Font size"><option value="2">Small</option><option value="3" selected>Normal</option><option value="4">Large</option><option value="5">Larger</option></select>
    </div>
    <div id="{{ $editorId }}" class="ja-review-editor" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Message to client" data-placeholder="Write your message to the client…"></div>
</div>
