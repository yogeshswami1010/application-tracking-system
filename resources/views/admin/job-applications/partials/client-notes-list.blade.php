
@forelse($clientNotes as $note)
<div class="ja-note" id="cn-note-{{ $note->id }}" style="border-left-color: #059669;">
    <div class="ja-note-meta">
        <span class="ja-note-author">
            <i class="fa fa-user-circle" style="font-size:14px;color:#059669"></i>
            {{ ucwords($note->user->name) }}
        </span>
        <span class="ja-note-time">
            {{ $note->created_at->format('d M Y, h:i A') }}
        </span>
    </div>
    <p class="cn-note-text ja-note-body">{{ ucfirst($note->note_text) }}</p>
    <div class="cn-note-textarea"></div>
    @if(auth()->user()->cans('edit_job_applications'))
    <div class="ja-note-actions">
        <button class="edit-client-note ja-note-btn" data-note-id="{{ $note->id }}">
            <i class="fa fa-pencil"></i> Edit
        </button>
        <button class="delete-client-note ja-note-btn" data-note-id="{{ $note->id }}" style="color:#EF4444">
            <i class="fa fa-trash"></i> Delete
        </button>
    </div>
    @endif
</div>
@empty
<div style="text-align:center;padding:24px 0;color:#B0B8C4;font-size:12.5px">
    <i class="fa fa-sticky-note-o" style="font-size:24px;display:block;margin-bottom:8px;opacity:.4"></i>
    No client notes yet for this job.
</div>
@endforelse