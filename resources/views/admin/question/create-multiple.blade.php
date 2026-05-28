@extends('layouts.app')

@section('content')

    <div class="flex flex-col">
        <div class="w-full">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">@lang('app.createNew')</h4>

                    <form class="ajax-form space-y-4" method="POST" id="createForm">
                        @csrf

                    <div id="education_fields"></div>
                    <div class="space-y-4">

                        <div id="addMoreBox1" class="w-full">
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <div class="w-full mb-4">
                                        <div id="dateBox" class="mb-4">
                                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">@lang('app.question')</label>
                                            <input type="text" name="name[]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" placeholder="@lang('menu.question')">
                                        </div>
                                    </div>
                                    <div class="w-full">
                                        <div class="mb-4">
                                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">@lang('app.required')</label>
                                            <select name="required[]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                                                <option value="yes">@lang('app.yes')</option>
                                                <option value="no">@lang('app.no')</option>
                                            </select>
                                        </div>

                                    </div>
                                </div>

                                <div class="flex items-start pt-8">
                                    <button type="button"  onclick="removeBox(1)"  class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 text-sm"><i class="fa fa-times"></i></button>
                                </div>
                            </div>

                        </div>
                        <div id="insertBefore"></div>

                        <div class="w-full">
                            <button type="button" id="plusButton" class="px-3 py-1.5 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm mb-5">
                                @lang('app.addMore') <i class="fa fa-plus"></i>
                            </button>
                        </div>
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

    var $insertBefore = $('#insertBefore');
    var $i = 0;

    // Add More Inputs
    $('#plusButton').click(function(){

        $i = $i+1;
        var indexs = $i+1;
        $(' <div id="addMoreBox'+indexs+'" class="w-full"> ' +
            '<div class="flex gap-4">' +
            '<div class="flex-1">' +
            '<div class="w-full mb-4">' +
        '<div id="dateBox" class="mb-4">' +
            '<label for="name" class="block text-sm font-medium text-gray-700 mb-1">@lang('app.question')</label>' +
            '<input type="text" name="name['+$i+']" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" placeholder="@lang('menu.question')">' +
            '</div>' +
            '</div>' +
            '<div class="w-full">' +
        '<div class="mb-4">' +
            '<label for="address" class="block text-sm font-medium text-gray-700 mb-1">@lang('app.required')</label>' +
            '<select name="required['+$i+']" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">' +
            '<option value="yes">@lang('app.yes')</option>' +
            '<option value="no">@lang('app.no')</option>' +
            '</select>' +
            '</div>' +
            '</div>' +
            '</div>' +

            '<div class="flex items-start pt-8">' +
        '<button type="button"  onclick="removeBox('+indexs+')"  class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 text-sm"><i class="fa fa-times"></i></button>' +
        '</div>' +
        '</div>').insertBefore($insertBefore);

    });

    // Remove fields
    function removeBox(index){
        $('#addMoreBox'+index).remove();
    }
    // // Remove fields
    // function removeBox(index){
    //     $('#addMoreBox'+index).remove();
    // }
    //
    // function remove_education_fields(rid) {
    //     $('.removeclass' + rid).remove();
    // }

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