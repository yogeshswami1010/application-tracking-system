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
                            <input type="text" name="question" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" placeholder="@lang('menu.question')">
                        </div>
                        <div class="mb-4">
                            <label for="required" class="block text-sm font-medium text-gray-700 mb-1">@lang('app.required')</label>
                            <select name="required" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" id="required">
                                <option value="yes">@lang('app.yes')</option>
                                <option value="no">@lang('app.no')</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">@lang('app.type')</label>
                            <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" id="type">
                                <option value="text">@lang('app.text')</option>
                                <option value="file">@lang('app.file')</option>
                                <option value="radio">@lang('Radio')</option>  {{-- ✅ Added radio option --}}
                            </select>
                        </div>

                        {{-- ✅ Radio Options section (shown only when type = radio) --}}
                        <div class="mb-4 hidden" id="radio-options-wrapper">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                @lang('Radio Options')
                            </label>
                        
                            <div id="radio-options-list" class="space-y-2">
                            </div>
                        
                        </div>

                        <div class="mb-4">
                            <label for="job_category_id" class="block text-sm font-medium text-gray-700 mb-1">@lang('menu.jobCategories')</label>
                            <select name="job_category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" id="job_category_id">
                                <option value="">@lang('app.all')</option>
                                @foreach(($categories ?? []) as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" id="save-form" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"><i class="fa fa-check"></i> @lang('app.save')</button>
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

            <div class="flex items-center gap-2 radio-option-row">
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

    $('#save-form').click(function () {

        $.easyAjax({
            url: '{{route('admin.questions.store')}}',
            container: '#createForm',
            type: "POST",
            redirect: true,
            data: $('#createForm').serialize()
        })

    });

</script>
@endpush