@extends('layouts.app')

@section('content')

<div class="flex flex-col">
    <div class="w-full">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">

                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    @lang('app.edit')
                </h4>

                <form class="ajax-form space-y-4" method="POST" id="createForm">
                    @csrf
                    <input name="_method" type="hidden" value="PUT">

                    {{-- Question --}}
                    <div class="mb-4">
                        <label for="question" class="block text-sm font-medium text-gray-700 mb-1">
                            @lang('menu.question')
                        </label>
                        <input type="text" name="question" value="{{ $question->question }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            placeholder="@lang('menu.question')" id="question">
                    </div>

                    {{-- Required --}}
                    <div class="mb-4">
                        <label for="required" class="block text-sm font-medium text-gray-700 mb-1">
                            @lang('app.required')
                        </label>
                        <select name="required"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            id="required">
                            <option @if($question->required == 'yes') selected @endif value="yes">@lang('app.yes')</option>
                            <option @if($question->required == 'no') selected @endif value="no">@lang('app.no')</option>
                        </select>
                    </div>

                    {{-- Question Type --}}
                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            @lang('app.type')
                        </label>
                        <select name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            id="type">
                            <option @if($question->type == 'text') selected @endif value="text">@lang('app.text')</option>
                            <option @if($question->type == 'file') selected @endif value="file">@lang('app.file')</option>
                            <option @if($question->type == 'radio') selected @endif value="radio">Radio</option>
                        </select>
                    </div>

                    {{-- ✅ Knockout Toggle — visible only for radio type --}}
                    <div class="mb-4 {{ $question->type == 'radio' ? '' : 'hidden' }}" id="knockout-wrapper">
                        <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-md">
                            <label class="relative inline-flex items-center cursor-pointer mt-0.5">
                                <input type="checkbox" name="is_knockout" id="is_knockout" value="1"
                                    class="sr-only peer"
                                    @if($question->is_knockout) checked @endif>
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer
                                    peer-focus:ring-2 peer-focus:ring-amber-300
                                    peer-checked:bg-amber-500
                                    after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                    after:bg-white after:border-gray-300 after:border after:rounded-full
                                    after:h-5 after:w-5 after:transition-all
                                    peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                            <div>
                                <span class="text-sm font-semibold text-amber-800">Knockout Question</span>
                                <p class="text-xs text-amber-600 mt-0.5">
                                    If the applicant selects the knockout answer, their application will be marked as
                                    <span class="font-semibold text-red-600">Rejected</span>.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Radio Options --}}
                    <div class="mb-4 {{ $question->type == 'radio' ? '' : 'hidden' }}" id="radio-options-wrapper">

                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Radio Options</label>
                            <button type="button" id="add-radio-option"
                                class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                                <i class="fa fa-plus mr-1"></i> Add Option
                            </button>
                        </div>

                        {{-- Column header — shown only when knockout is ON --}}
                        <div id="knockout-col-header"
                            class="{{ $question->is_knockout ? '' : 'hidden' }} mb-1 px-1">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 text-xs text-gray-400 pl-1">Option Label</div>
                                <div class="w-32 text-center text-xs font-semibold text-red-500">Knockout Answer</div>
                                <div class="w-7"></div>
                            </div>
                        </div>

                        <div id="radio-options-list" class="space-y-2"></div>

                        {{-- Validation hint --}}
                        <p id="knockout-hint" class="hidden mt-2 text-xs text-red-500">
                            <i class="fa fa-exclamation-circle mr-1"></i>
                            Please select which option is the knockout answer.
                        </p>

                    </div>

                    {{-- Job Category --}}
                    <div class="mb-4">
                        <label for="job_category_id" class="block text-sm font-medium text-gray-700 mb-1">
                            @lang('menu.jobCategories')
                        </label>
                        <select name="job_category_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            id="job_category_id">
                            <option value="">@lang('app.all')</option>
                            @foreach(($categories ?? []) as $category)
                                <option value="{{ $category->id }}"
                                    @if((int) $question->job_category_id === (int) $category->id) selected @endif>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Save --}}
                    <button type="button" id="save-form"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fa fa-check"></i> @lang('app.save')
                    </button>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('footer-script')
<script>
    const existingOptions   = {{ json_encode($question->type === 'radio' && $question->answer_type ? array_values(array_filter(array_map('trim', explode(',', $question->answer_type)))) : ['Yes', 'No']) }};
    const existingKnockout  = {{ json_encode($question->knockout_answer ?? '') }};
    const isKnockoutChecked = {{ json_encode((bool) $question->is_knockout) }};

    let optionCounter = 0;

    function isKnockoutEnabled() {
        return $('#is_knockout').is(':checked');
    }

    function buildOptionRow(value) {
        optionCounter++;
        const uid        = optionCounter;
        const isKnockout = isKnockoutEnabled();
        const isChecked  = isKnockout && existingKnockout !== '' && value.trim() === existingKnockout.trim();

        return `
            <div class="flex items-center gap-2 radio-option-row" data-uid="${uid}">

                <input type="text"
                       name="radio_options[]"
                       value="${$('<div>').text(value).html()}"
                       placeholder="Enter option label"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm
                              focus:outline-none focus:ring-primary focus:border-primary text-sm option-input">

                <div class="knockout-col w-32 justify-center ${isKnockout ? 'flex' : 'hidden'}">
                    <label class="flex items-center gap-1.5 cursor-pointer select-none">
                        <input type="radio"
                               name="knockout_answer"
                               value="${$('<div>').text(value).html()}"
                               class="knockout-radio accent-red-500 w-4 h-4"
                               ${isChecked ? 'checked' : ''}>
                        <span class="text-xs text-red-600 font-medium">Set as knockout</span>
                    </label>
                </div>

                <button type="button"
                        class="remove-option w-7 h-7 flex items-center justify-center text-gray-400
                               hover:text-red-500 hover:bg-red-50 rounded focus:outline-none transition-colors"
                        title="Remove option">
                    <i class="fa fa-times text-xs"></i>
                </button>

            </div>
        `;
    }

    function appendOption(value) {
        $('#radio-options-list').append(buildOptionRow(value || ''));
    }

    function loadOptions(options) {
        $('#radio-options-list').html('');
        optionCounter = 0;
        options.forEach(function (v) { appendOption(v); });
    }

    function syncRadioValues() {
        $('.radio-option-row').each(function () {
            const val = $(this).find('.option-input').val();
            $(this).find('.knockout-radio').val(val);
        });
    }

    function syncKnockoutColumns() {
        const on = isKnockoutEnabled();
        if (on) {
            $('#knockout-col-header').removeClass('hidden');
            $('.knockout-col').removeClass('hidden').addClass('flex');
        } else {
            $('#knockout-col-header').addClass('hidden');
            $('.knockout-col').addClass('hidden').removeClass('flex');
            $('input[name="knockout_answer"]').prop('checked', false);
        }
    }

    $(document).ready(function () {
        if ($('#type').val() === 'radio') {
            loadOptions(existingOptions);
            syncKnockoutColumns();
        }
    });

    $('#type').on('change', function () {
        if ($(this).val() === 'radio') {
            $('#radio-options-wrapper').removeClass('hidden');
            $('#knockout-wrapper').removeClass('hidden');
            loadOptions(['Yes', 'No']);
            syncKnockoutColumns();
        } else {
            $('#radio-options-wrapper').addClass('hidden');
            $('#knockout-wrapper').addClass('hidden');
            $('#radio-options-list').html('');
            $('#is_knockout').prop('checked', false);
            syncKnockoutColumns();
        }
    });

    $('#is_knockout').on('change', function () {
        syncKnockoutColumns();
        $('#knockout-hint').addClass('hidden');
    });

    $('#add-radio-option').on('click', function () {
        appendOption('');
        syncKnockoutColumns();
        syncRadioValues();
    });

    $(document).on('click', '.remove-option', function () {
        if ($('.radio-option-row').length <= 2) {
            alert('A radio question must have at least 2 options.');
            return;
        }
        $(this).closest('.radio-option-row').remove();
    });

    $(document).on('input', '.option-input', function () {
        const row = $(this).closest('.radio-option-row');
        row.find('.knockout-radio').val($(this).val());
    });

    $('#save-form').on('click', function () {
        if (isKnockoutEnabled() && $('#type').val() === 'radio') {
            syncRadioValues();
            const chosen = $('input[name="knockout_answer"]:checked').val();
            if (!chosen || chosen.trim() === '') {
                $('#knockout-hint').removeClass('hidden');
                return;
            }
        }

        $('#knockout-hint').addClass('hidden');

        $.easyAjax({
            url: '{{ route('admin.questions.update', $question->id) }}',
            container: '#createForm',
            type: "POST",
            redirect: true,
            data: $('#createForm').serialize()
        });
    });
</script>
@endpush