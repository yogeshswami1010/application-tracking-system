@if(count($jobQuestion) > 0)
    @forelse($jobQuestion as $question)

        <div class="form-group">

            <label class="block text-sm font-medium text-gray-700 mb-1"
                   for="answer[{{ $question->id}}]">

                {{ $question->question }}

            </label>

            {{-- TEXT QUESTION --}}
            @if($question->type == 'text')

                <input
                    class="form-control"
                    type="text"
                    id="answer[{{ $question->id}}]"
                    name="answer[{{ $question->id}}]"
                    placeholder="@lang('modules.front.yourAnswer')"
                    value="{{ (!is_null($question->answers->first())) ? $question->answers->first()->answer : null }}"
                >

            {{-- RADIO QUESTION --}}
            @elseif($question->type == 'radio')

                <div class="flex items-center gap-6 mt-2">

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="answer[{{ $question->id }}]"
                            value="Yes"
                            {{ (!is_null($question->answers->first()) && strtolower($question->answers->first()->answer) == 'yes') ? 'checked' : '' }}
                        >
                        <span>Yes</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="answer[{{ $question->id }}]"
                            value="No"
                            {{ (!is_null($question->answers->first()) && strtolower($question->answers->first()->answer) == 'no') ? 'checked' : '' }}
                        >
                        <span>No</span>
                    </label>

                </div>

            {{-- FILE QUESTION --}}
            @else

                @if(is_null($question->answers->first()))
                    <input type="hidden" name="answer[{{ $question->id}}]">
                @endif

                <input
                    class="select-file"
                    accept=".png,.jpg,.jpeg,.pdf,.doc,.docx,.xls,.xlsx,.rtf"
                    type="file"
                    id="answer[{{ $question->id}}]"
                    name="answer[{{ $question->id}}]"
                >

                <p class="text-xs text-gray-600 mt-1">
                    @lang('modules.front.resumeFileType')
                </p>

                @if(!is_null($question->answers->first()))
                    <a target="_blank"
                       href="{{ $question->answers->first()->file_url }}"
                       class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm inline-flex items-center mt-2">

                        @lang('app.view') @lang('app.file')

                    </a>
                @endif

            @endif

        </div>

    @empty
    @endforelse
@endif