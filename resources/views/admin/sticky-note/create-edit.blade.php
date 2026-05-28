@php
    $noteId = data_get($noteDetail, 'id');
    $isEdit = filled($noteId);
    $noteColour = data_get($noteDetail, 'colour');
    $currentColour = $isEdit && $noteColour !== null && $noteColour !== '' ? $noteColour : 'blue';
@endphp
<div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
    <h2 class="text-lg font-bold text-slate-900" id="sticky-modal-title">
        @if($isEdit)
            @lang('app.edit') @lang('app.note')
        @else
            @lang('app.addNew') @lang('app.note')
        @endif
    </h2>
    <button
        type="button"
        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border-2 border-blue-500 text-blue-600 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
        onclick="$('#responsive-modal').addClass('hidden').css({display:'',visibility:'',opacity:''})"
        aria-label="@lang('app.close')"
    >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

<div class="space-y-5 px-6 py-5">
    <div>
        <label for="notetext" class="mb-2 block text-sm font-medium text-slate-700">@lang('app.note'):</label>
        <textarea
            class="min-h-[140px] w-full resize-y rounded-md border border-gray-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
            id="notetext"
            name="notetext"
            rows="5"
        >@if($isEdit){{ data_get($noteDetail, 'note_text') }}@endif</textarea>
        <input type="hidden" id="stickyID" value="{{ $isEdit ? $noteId : '' }}">
        <input type="hidden" id="stickyColor" value="{{ $isEdit ? $noteColour : '' }}">
    </div>

    <div>
        <p class="mb-3 text-sm font-medium text-slate-700">@lang('modules.sticky.colors')</p>
        <div class="flex flex-wrap gap-2" id="sticky-color-swatches" role="list">
            <button
                type="button"
                id="red"
                data-sticky-swatch
                onclick="selectColor('red')"
                class="h-9 w-9 shrink-0 rounded-md bg-red-500 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 @if($currentColour === 'red') ring-2 ring-offset-2 ring-slate-800 @endif"
                aria-pressed="{{ $currentColour === 'red' ? 'true' : 'false' }}"
                aria-label="Red"
            ></button>
            <button
                type="button"
                id="green"
                data-sticky-swatch
                onclick="selectColor('green')"
                class="h-9 w-9 shrink-0 rounded-md bg-emerald-600 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 @if($currentColour === 'green') ring-2 ring-offset-2 ring-slate-800 @endif"
                aria-pressed="{{ $currentColour === 'green' ? 'true' : 'false' }}"
                aria-label="Green"
            ></button>
            <button
                type="button"
                id="blue"
                data-sticky-swatch
                onclick="selectColor('blue')"
                class="h-9 w-9 shrink-0 rounded-md bg-blue-500 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 @if($currentColour === 'blue') ring-2 ring-offset-2 ring-slate-800 @endif"
                aria-pressed="{{ $currentColour === 'blue' ? 'true' : 'false' }}"
                aria-label="Blue"
            ></button>
            <button
                type="button"
                id="yellow"
                data-sticky-swatch
                onclick="selectColor('yellow')"
                class="h-9 w-9 shrink-0 rounded-md bg-orange-600 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 @if($currentColour === 'yellow') ring-2 ring-offset-2 ring-slate-800 @endif"
                aria-pressed="{{ $currentColour === 'yellow' ? 'true' : 'false' }}"
                aria-label="Yellow"
            ></button>
            <button
                type="button"
                id="purple"
                data-sticky-swatch
                onclick="selectColor('purple')"
                class="h-9 w-9 shrink-0 rounded-md bg-purple-500 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-400 @if($currentColour === 'purple') ring-2 ring-offset-2 ring-slate-800 @endif"
                aria-pressed="{{ $currentColour === 'purple' ? 'true' : 'false' }}"
                aria-label="Purple"
            ></button>
        </div>
        <p class="mt-2 text-sm text-gray-500">@lang('messages.defaultColorNote')</p>
    </div>
</div>

<div class="flex items-center justify-end border-t border-gray-100 px-6 py-4">
    <button
        type="button"
        onclick="addOrEditStickyNote('{{ $isEdit ? $noteId : '' }}')"
        class="inline-flex items-center justify-center rounded-md bg-red-600 px-8 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
    >
        @if($isEdit) @lang('app.update') @else @lang('app.save') @endif
    </button>
</div>
