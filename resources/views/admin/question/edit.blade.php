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

                    <div id="education_fields"></div>

                    {{-- Question --}}
                    <div class="mb-4">
                        <label for="question"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            @lang('menu.question')
                        </label>

                        <input
                            type="text"
                            name="question"
                            value="{{ $question->question }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            placeholder="@lang('menu.question')"
                            id="question"
                        >
                    </div>

                    {{-- Required --}}
                    <div class="mb-4">
                        <label for="required"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            @lang('app.required')
                        </label>

                        <select
                            name="required"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            id="required"
                        >
                            <option
                                @if($question->required == 'yes') selected @endif
                                value="yes">
                                @lang('app.yes')
                            </option>

                            <option
                                @if($question->required == 'no') selected @endif
                                value="no">
                                @lang('app.no')
                            </option>
                        </select>
                    </div>

              
                    {{-- Question Type --}}
                    <div class="mb-4">
                        <label for="type"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            @lang('app.type')
                        </label>
                    
                        <select
                            name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                            id="type"
                        >
                            <option
                                @if($question->type == 'text') selected @endif
                                value="text">
                                @lang('app.text')
                            </option>
                    
                            <option
                                @if($question->type == 'file') selected @endif
                                value="file">
                                @lang('app.file')
                            </option>
                    
                            <option
                                @if($question->type == 'radio') selected @endif
                                value="radio">
                                Radio
                            </option>
                        </select>
                    </div>
                    
                    {{-- Radio Options --}}
                    <div class="mb-4 {{ $question->type == 'radio' ? '' : 'hidden' }}"
                         id="radio-options-wrapper">
                    
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Radio Options
                        </label>
                    
                        <div id="radio-options-list">
                    
                            <div class="flex items-center gap-2 radio-option-row mb-2">
                                <input type="text"
                                       name="radio_options[]"
                                       value="Yes"
                                       readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                            </div>
                    
                            <div class="flex items-center gap-2 radio-option-row">
                                <input type="text"
                                       name="radio_options[]"
                                       value="No"
                                       readonly
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                            </div>
                    
                        </div>
                    </div>

                    {{-- Answer Type --}}
                    <div class="mb-4">
                        <label for="answer_type"
                            class="block text-sm font-medium text-gray-700 mb-1">
                            Answer Type
                        </label>

                        <select
                            name="answer_type"
                            id="answer_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                        >
                            <option
                                value="text"
                                @if($question->answer_type == 'text') selected @endif>
                                Text Answer
                            </option>

                            <option
                                value="yes_no"
                                @if($question->answer_type == 'yes_no') selected @endif>
                                Yes / No
                            </option>
                        </select>
                    </div>

                    {{-- Job Category --}}
                    <div class="mb-4">
                        <label for="job_category_id"
                            class="block text-sm font-medium text-gray-700 mb-1">
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
                                    @if((int) $question->job_category_id === (int) $category->id) selected @endif
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Save Button --}}
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

    function loadDefaultRadioOptions() {

        $('#radio-options-list').html(`

            <div class="flex items-center gap-2 radio-option-row mb-2">
                <input type="text"
                       name="radio_options[]"
                       value="Yes"
                       readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
            </div>

            <div class="flex items-center gap-2 radio-option-row">
                <input type="text"
                       name="radio_options[]"
                       value="No"
                       readonly
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100">
            </div>

        `);

    }

    $('#type').on('change', function () {

        if ($(this).val() === 'radio') {

            $('#radio-options-wrapper').removeClass('hidden');

            loadDefaultRadioOptions();

        } else {

            $('#radio-options-wrapper').addClass('hidden');

            $('#radio-options-list').html('');

        }

    });

    // Load existing radio options on page load
    $(document).ready(function () {

        if ($('#type').val() === 'radio') {

            loadDefaultRadioOptions();

        }

    });

    $('#save-form').click(function () {

        $.easyAjax({
            url: '{{ route('admin.questions.update', $question->id) }}',
            container: '#createForm',
            type: "POST",
            redirect: true,
            data: $('#createForm').serialize()
        })

    });

</script>

@endpush