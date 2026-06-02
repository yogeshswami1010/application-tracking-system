@foreach($stickyNotes as $note)
    <div class="w-full sticky-note mb-3" id="stickyBox_{{$note->id}}">
        <div class="rounded-xl border border-white/10 p-4 shadow-sm bg-{{$note->colour}} b-none">
            <p class="text-sm">{!! nl2br($note->note_text) !!}</p>
            <hr class="my-3 border-white/20">
            <div class="flex flex-col gap-2 text-xs">
                <div>
                    @lang("modules.sticky.lastUpdated"): {{ $note->updated_at->diffForHumans() }}
                </div>
                <div class="mt-1 flex flex-wrap gap-3">
                    <a href="javascript:;" class=" hover:underline" onclick="showEditNoteModal({{$note->id}})"><i class="ti-pencil-alt"></i> @lang('app.edit')</a>
                    <a href="javascript:;" class=" hover:underline" onclick="deleteSticky({{$note->id}})"><i class="ti-close"></i> @lang('app.delete')</a>
                </div>
            </div>
        </div>
    </div>
@endforeach
