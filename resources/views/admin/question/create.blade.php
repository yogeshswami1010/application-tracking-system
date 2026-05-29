@extends('layouts.app')

@section('content')

    <div class="flex flex-col">
        <div class="w-full">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">@lang('app.createNew')</h4>

                    <form class="ajax-form space-y-4" method="POST" id="createForm">
                        @csrf

                        <div class="mb-4">
                            <label for="question" class="block text-sm font-medium text-gray-700 mb-1 required">@lang('menu.question')</label>
                            <input type="text" name="question"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                                placeholder="@lang('menu.question')">
                        </div>

                        <div class="mb-4">
                            <label for="required" class="block text-sm font-medium text-gray-700 mb-1">@lang('app.required')</label>
                            <select name="required"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                                id="required">
                                <option value="yes">@lang('app.yes')</option>
                                <option value="no">@lang('app.no')</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">@lang('app.type')</label>
                            <select name="type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                                id="type">
                                <option value="text">@lang('app.text')</option>
                                <option value="file">@lang('app.file')</option>
                                <option value="radio">Radio</option>
                            </select>
                        </div>

                        {{-- ✅ Knockout Question Toggle — only shown for radio type --}}
                        <div class="mb-4 hidden" id="knockout-wrapper">
                            <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-md">
                                <label class="relative inline-flex items-center cursor-pointer mt-0.5">
                                    <input type="checkbox" name="is_knockout" id="is_knockout" value="1" class="sr-only peer">
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

                        {{-- ✅ Radio Options section --}}
                        <div class="mb-4 hidden" id="radio-options-wrapper">

                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Radio Options</label>
                                <button type="button" id="add-radio-option"
                                    class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                                    <i class="fa fa-plus mr-1"></i> Add Option
                                </button>
                            </div>

                            {{-- Column header (shown only when knockout is enabled) --}}
                            <div id="knockout-col-header" class="hidden mb-1 px-1">
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

                        <div class="mb-4">
                            <label for="job_category_id" class="block text-sm font-medium text-gray-700 mb-1">@lang('menu.jobCategories')</label>
                            <select name="job_category_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                                id="job_category_id">
                                <option value="">@lang('app.all')</option>
                                @foreach(($categories ?? []) as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

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
    let optionCounter = 0;

    function isKnockoutEnabled() {
        return $('#is_knockout').is(':checked');
    }

    // Build a single option row
    function buildOptionRow(value) {
        optionCounter++;
        const uid = optionCounter;

        return `
            <div class="flex items-center gap-2 radio-option-row" data-uid="${uid}">

                <input type="text"
                       name="radio_options[]"
                       value="${value}"
                       placeholder="Enter option label"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm
                              focus:outline-none focus:ring-primary focus:border-primary text-sm option-input">

                {{-- Knockout radio — hidden until knockout toggle is ON --}}
                <div class="knockout-col w-32 justify-center ${isKnockoutEnabled() ? 'flex' : 'hidden'}">
                    <label class="flex items-center gap-1.5 cursor-pointer select-none">
                        <input type="radio"
                               name="knockout_answer"
                               value="${value}"
                               class="knockout-radio accent-red-500 w-4 h-4">
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

    function loadDefaultOptions() {
        $('#radio-options-list').html('');
        optionCounter = 0;
        appendOption('Yes');
        appendOption('No');
    }

    function appendOption(value) {
        $('#radio-options-list').append(buildOptionRow(value || ''));
    }

    // Keep each knockout radio's value in sync with its text input
    function syncRadioValues() {
        $('.radio-option-row').each(function () {
            const val = $(this).find('.option-input').val();
            $(this).find('.knockout-radio').val(val);
        });
    }

    // Show/hide the knockout column across all rows
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

    // ── Event: type select changes ──────────────────────────────────────────
    $('#type').on('change', function () {
        if ($(this).val() === 'radio') {
            $('#radio-options-wrapper').removeClass('hidden');
            $('#knockout-wrapper').removeClass('hidden');
            loadDefaultOptions();
            syncKnockoutColumns();
        } else {
            $('#radio-options-wrapper').addClass('hidden');
            $('#knockout-wrapper').addClass('hidden');
            $('#radio-options-list').html('');
            $('#is_knockout').prop('checked', false);
            syncKnockoutColumns();
        }
    });

    // ── Event: knockout toggle changes ─────────────────────────────────────
    $('#is_knockout').on('change', function () {
        syncKnockoutColumns();
        $('#knockout-hint').addClass('hidden');
    });

    // ── Event: Add Option button ───────────────────────────────────────────
    $('#add-radio-option').on('click', function () {
        appendOption('');
        syncKnockoutColumns();
        syncRadioValues();
    });

    // ── Event: Remove option ───────────────────────────────────────────────
    $(document).on('click', '.remove-option', function () {
        if ($('.radio-option-row').length <= 2) {
            alert('A radio question must have at least 2 options.');
            return;
        }
        $(this).closest('.radio-option-row').remove();
    });

    // ── Event: Option label typed — keep knockout value in sync ───────────
    $(document).on('input', '.option-input', function () {
        const row = $(this).closest('.radio-option-row');
        row.find('.knockout-radio').val($(this).val());
    });

    // ── Save ───────────────────────────────────────────────────────────────
    $('#save-form').on('click', function () {

        // If knockout is ON, a knockout answer must be selected
        if (isKnockoutEnabled() && $('#type').val() === 'radio') {
            syncRadioValues(); // make sure values are fresh
            const chosen = $('input[name="knockout_answer"]:checked').val();
            if (!chosen || chosen.trim() === '') {
                $('#knockout-hint').removeClass('hidden');
                return;
            }
        }

        $('#knockout-hint').addClass('hidden');

        $.easyAjax({
            url: '{{ route('admin.questions.store') }}',
            container: '#createForm',
            type: "POST",
            redirect: true,
            data: $('#createForm').serialize()
        });

    });
</script>
@endpush