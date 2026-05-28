@if (count($questions) > 0)
    @foreach ($questions as $question)
        <div class="form-group mb-0">
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-100 bg-[#F8F7F4]/50 px-4 py-3 text-[13px] text-slate-600 transition hover:border-gray-200">
                <input type="checkbox" value="{{ $question->id }}" name="question[]" @if (in_array($question->id, $jobQuestion ?? [])) checked @endif class="flat-red mt-0.5 rounded border-gray-300 text-primary focus:ring-primary">
                <span>{{ ucfirst($question->question) }} @if ($question->required == 'yes')<span class="text-slate-400">(@lang('app.required'))</span>@endif</span>
            </label>
        </div>
    @endforeach
@else
    <p class="text-[13px] text-slate-400">No matching questions found for selected category.</p>
@endif

