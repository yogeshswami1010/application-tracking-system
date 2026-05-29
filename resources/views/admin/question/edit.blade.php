@extends('layouts.app')

@section('content')

@php
    $existingOptions = ['Yes', 'No'];

    if (
        isset($question) &&
        $question &&
        optional($question)->type === 'radio' &&
        !empty($question->answer_type)
    ) {
        $existingOptions = array_values(
            array_filter(
                array_map('trim', explode(',', $question->answer_type))
            )
        );
    }
@endphp

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

                        <input
                            type="text"
                            name="question"
                            value="{{ old('question', optional($question)->question) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            placeholder="@lang('menu.question')"
                            id="question"
                        >
                    </div>

                    {{-- Required --}}
                    <div class="mb-4">
                        <label for="required" class="block text-sm font-medium text-gray-700 mb-1">
                            @lang('app.required')
                        </label>

                        <select
                            name="required"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            id="required"
                        >
                            <option
                                @if(optional($question)->required == 'yes') selected @endif
                                value="yes">
                                @lang('app.yes')
                            </option>

                            <option
                                @if(optional($question)->required == 'no') selected @endif
                                value="no">
                                @lang('app.no')
                            </option>
                        </select>
                    </div>

                    {{-- Question Type --}}
                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            @lang('app.type')
                        </label>

                        <select
                            name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            id="type"
                        >
                            <option
                                @if(optional($question)->type == 'text') selected @endif
                                value="text">
                                @lang('app.text')
                            </option>

                            <option
                                @if(optional($question)->type == 'file') selected @endif
                                value="file">
                                @lang('app.file')
                            </option>

                            <option
                                @if(optional($question)->type == 'radio') selected @endif
                                value="radio">
                                Radio
                            </option>
                        </select>
                    </div>

                    {{-- Knockout Toggle --}}
                    <div
                        class="mb-4 {{ optional($question)->type == 'radio' ? '' : 'hidden' }}"
                        id="knockout-wrapper"
                    >
                        <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-md">

                            <label class="relative inline-flex items-center cursor-pointer mt-0.5">

                                <input
                                    type="checkbox"
                                    name="is_knockout"
                                    id="is_knockout"
                                    value="1"
                                    class="sr-only peer"
                                    @if(optional($question)->is_knockout) checked @endif
                                >

                                <div class="w-11 h-6 bg-gray-200 rounded-full peer
                                    peer-focus:ring-2 peer-focus:ring-amber-300
                                    peer-checked:bg-amber-500
                                    after:content-[''] after:absolute after:top-[2px]
                                    after:left-[2px] after:bg-white after:border-gray-300
                                    after:border after:rounded-full after:h-5 after:w-5
                                    after:transition-all peer-checked:after:translate-x-full
                                    peer-checked:after:border-white">
                                </div>

                            </label>

                            <div>
                                <span class="text-sm font-semibold text-amber-800">
                                    Knockout Question
                                </span>

                                <p class="text-xs text-amber-600 mt-0.5">
                                    If the applicant selects the knockout answer,
                                    their application will be marked as
                                    <span class="font-semibold text-red-600">
                                        Rejected
                                    </span>.
                                </p>
                            </div>

                        </div>
                    </div>

                    {{-- Radio Options --}}
                    <div
                        class="mb-4 {{ optional($question)->type == 'radio' ? '' : 'hidden' }}"
                        id="radio-options-wrapper"
                    >

                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Radio Options
                            </label>

                            <button
                                type="button"
                                id="add-radio-option"
                                class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none"
                            >
                                <i class="fa fa-plus mr-1"></i>
                                Add Option
                            </button>
                        </div>

                        {{-- Knockout Header --}}
                        <div
                            id="knockout-col-header"
                            class="{{ optional($question)->is_knockout ? '' : 'hidden' }} mb-1 px-1"
                        >
                            <div class="flex items-center gap-2">

                                <div class="flex-1 text-xs text-gray-400 pl-1">
                                    Option Label
                                </div>

                                <div class="w-32 text-center text-xs font-semibold text-red-500">
                                    Knockout Answer
                                </div>

                                <div class="w-7"></div>

                            </div>
                        </div>

                        <div id="radio-options-list" class="space-y-2"></div>

                        <p id="knockout-hint" class="hidden mt-2 text-xs text-red-500">
                            <i class="fa fa-exclamation-circle mr-1"></i>
                            Please select which option is the knockout answer.
                        </p>

                    </div>

                    {{-- Job Category --}}
                    <div class="mb-4">

                        <label
                            for="job_category_id"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            @lang('menu.jobCategories')
                        </label>

                        <select
                            name="job_category_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            id="job_category_id"
                        >

                            <option value="">
                                @lang('app.all')
                            </option>

                            @foreach(($categories ?? []) as $category)

                                <option
                                    value="{{ $category->id }}"
                                    @if((int) optional($question)->job_category_id === (int) $category->id) selected @endif
                                >
                                    {{ $category->name }}
                                </option>

                            @endforeach

                        </select>
                    </div>

                    {{-- Save --}}
                    <button
                        type="button"
                        id="save-form"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    >
                        <i class="fa fa-check"></i>
                        @lang('app.save')
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>

@endsection

@push('footer-script')

<script>

    const existingOptions = @json($existingOptions);
    const existingKnockout = @json(optional($question)->knockout_answer ?? '');

    let optionCounter = 0;

    function isKnockoutEnabled() {
        return $('#is_knockout').is(':checked');
    }

    function buildOptionRow(value, isKnockout, savedKnockout) {

        optionCounter++;

        const saved = (savedKnockout || '').trim();

        const isChecked =
            isKnockout &&
            saved !== '' &&
            value.trim() === saved;

        const $row = $('<div>')
            .addClass('flex items-center gap-2 radio-option-row');

        const $input = $('<input>', {
            type: 'text',
            name: 'radio_options[]',
            placeholder: 'Enter option label'
        })
        .val(value)
        .addClass('flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary text-sm option-input');

        const $knockoutCol = $('<div>')
            .addClass('knockout-col w-32 justify-center')
            .addClass(isKnockout ? 'flex' : 'hidden');

        const $radioLabel = $('<label>')
            .addClass('flex items-center gap-1.5 cursor-pointer select-none');

        const $radio = $('<input>', {
            type: 'radio',
            name: 'knockout_answer'
        })
        .val(value)
        .addClass('knockout-radio accent-red-500 w-4 h-4');

        if (isChecked) {
            $radio.prop('checked', true);
        }

        $radioLabel.append(
            $radio,
            $('<span>')
                .addClass('text-xs text-red-600 font-medium')
                .text('Set as knockout')
        );

        $knockoutCol.append($radioLabel);

        const $removeBtn = $('<button>', {
            type: 'button',
            title: 'Remove option'
        })
        .addClass('remove-option w-7 h-7 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded focus:outline-none transition-colors')
        .html('<i class="fa fa-times text-xs"></i>');

        $row.append($input, $knockoutCol, $removeBtn);

        return $row;
    }

    function appendOption(value) {

        const isKnockout = isKnockoutEnabled();

        $('#radio-options-list').append(
            buildOptionRow(value || '', isKnockout, '')
        );
    }

    function loadOptions(options, isKnockout, savedKnockout) {

        $('#radio-options-list').empty();

        optionCounter = 0;

        const opts =
            (options && options.length > 0)
            ? options
            : ['Yes', 'No'];

        opts.forEach(function (v) {

            $('#radio-options-list').append(
                buildOptionRow(v, isKnockout, savedKnockout)
            );

        });
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

            $('.knockout-col')
                .removeClass('hidden')
                .addClass('flex');

        } else {

            $('#knockout-col-header').addClass('hidden');

            $('.knockout-col')
                .addClass('hidden')
                .removeClass('flex');

            $('input[name="knockout_answer"]').prop('checked', false);
        }
    }

    $(document).ready(function () {

        if ($('#type').val() === 'radio') {

            const isKnockout =
                $('#is_knockout').is(':checked');

            loadOptions(
                existingOptions,
                isKnockout,
                existingKnockout
            );

            syncKnockoutColumns();
        }
    });

    $('#type').on('change', function () {

        if ($(this).val() === 'radio') {

            $('#radio-options-wrapper').removeClass('hidden');

            $('#knockout-wrapper').removeClass('hidden');

            loadOptions(['Yes', 'No'], false, '');

            syncKnockoutColumns();

        } else {

            $('#radio-options-wrapper').addClass('hidden');

            $('#knockout-wrapper').addClass('hidden');

            $('#radio-options-list').empty();

            $('#is_knockout').prop('checked', false);

            syncKnockoutColumns();
        }
    });

    $('#is_knockout').on('change', function () {

        const isKnockout = $(this).is(':checked');

        const saved = isKnockout
            ? existingKnockout
            : '';

        const current = $('.radio-option-row .option-input')
            .map(function () {
                return $(this).val();
            }).get();

        loadOptions(
            current.length > 0 ? current : existingOptions,
            isKnockout,
            saved
        );

        syncKnockoutColumns();

        $('#knockout-hint').addClass('hidden');
    });

    $(document).on('click', '#add-radio-option', function () {

        appendOption('');

        syncKnockoutColumns();
    });

    $(document).on('click', '.remove-option', function () {

        if ($('.radio-option-row').length <= 2) {

            alert('A radio question must have at least 2 options.');

            return;
        }

        $(this).closest('.radio-option-row').remove();
    });

    $(document).on('input', '.option-input', function () {

        $(this)
            .closest('.radio-option-row')
            .find('.knockout-radio')
            .val($(this).val());

    });

    $('#save-form').on('click', function () {

        if (
            isKnockoutEnabled() &&
            $('#type').val() === 'radio'
        ) {

            syncRadioValues();

            const chosen =
                $('input[name="knockout_answer"]:checked').val();

            if (!chosen || chosen.trim() === '') {

                $('#knockout-hint').removeClass('hidden');

                return;
            }
        }

        $('#knockout-hint').addClass('hidden');

        $.easyAjax({

            url: '{{ route('admin.questions.update', optional($question)->id) }}',

            container: '#createForm',

            type: "POST",

            redirect: true,

            data: $('#createForm').serialize()

        });

    });

</script>

@endpush