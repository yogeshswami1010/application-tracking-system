<ul class="list-none space-y-3">
    @foreach ($notes as $note)
        <li class="flex items-start mb-3" id="note-{{ $note->id }}">
            <div class="flex-1">
                <h6 class="mt-0 mb-1 flex items-center justify-between">
                    <span>{{ ucwords($note->user->name) }}</span>
                    <span class="text-sm italic font-light flex items-center gap-2">
                        <small>{{ $note->created_at->diffForHumans() }}</small>
                        @if($user->cans('edit_job_applications'))
                            <a href="javascript:;" class="edit-note" data-note-id="{{ $note->id }}"><i class="fa fa-edit ml-2"></i></a>
                            <a href="javascript:;" class="delete-note" data-note-id="{{ $note->id }}"><i class="fa fa-trash ml-1 text-red-600"></i></a>
                        @endif
                    </span>
                </h6>
                <small class="note-text block">{{ ucfirst($note->note_text) }}</small>
                <div class="note-textarea"></div>
            </div>
        </li>
    @endforeach
</ul>