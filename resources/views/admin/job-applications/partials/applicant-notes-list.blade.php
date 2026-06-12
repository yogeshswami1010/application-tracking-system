@forelse($notes as $note)
<div class="ja-note" id="note-{{ $note->id }}">
    <div class="ja-note-meta">
        <span class="ja-note-author">
            <i class="fa fa-user-circle" style="font-size:14px;color:#2563EB"></i>
            {{ ucwords($note->user->name) }}
        </span>
        <span class="ja-note-time">
            {{ $note->created_at->timezone('America/Toronto')->format('d M Y, h:i A') }}
        </span>
    </div>
    <p class="note-text ja-note-body">{{ ucfirst($note->note_text) }}</p>
    <div class="note-textarea"></div>
    @if(auth()->user()->cans('edit_job_applications'))
    <div class="ja-note-actions">
        <button class="edit-note ja-note-btn" data-note-id="{{ $note->id }}">
            <i class="fa fa-pencil"></i> Edit
        </button>
        
        @if(auth()->user()->role_id === 1)
        <button class="delete-note ja-note-btn" data-note-id="{{ $note->id }}" style="color:#EF4444;border-color:#fecaca;">
            <i class="fa fa-trash"></i> Delete
        </button>
        @endif
    </div>
    @endif
</div>
@empty
<div style="text-align:center;padding:24px 0;color:#B0B8C4;font-size:12.5px">
    <i class="fa fa-sticky-note-o" style="font-size:24px;display:block;margin-bottom:8px;opacity:.4"></i>
    No notes yet.
</div>
@endforelse