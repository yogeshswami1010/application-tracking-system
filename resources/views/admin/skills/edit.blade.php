@extends('layouts.app')

@section('content')

    <div class="flex flex-col">
        <div class="w-full">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">@lang('app.edit')</h4>

                    <form class="ajax-form" method="POST" id="createForm">
                        @csrf

                        <input name="_method" type="hidden" value="PUT">

                        <div class="mb-4">
                            <div class="mb-4">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">@lang('menu.jobCategories')</label>
                                <select name="category_id" id="category_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary select2">
                                    @foreach($categories as $category)
                                        <option @if($skill->category_id == $category->id) selected @endif value="{{ $category->id }}">{{ ucfirst($category->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <div id="education_fields"></div>
                        <div class="mb-4">
                            <div class="mb-4">
                                <input type="text" name="name" value="{{ $skill->name }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                                       placeholder="@lang('menu.skills') @lang('app.name')">
                            </div>
                        </div>

                        <button type="button" id="save-form" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"><i
                                    class="fa fa-check"></i> @lang('app.save')</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ asset('assets/node_modules_files/bootstrap-select/bootstrap-select.min.js') }}"
            type="text/javascript"></script>

    <script>
        // For select 2
        $(".select2").select2();

        var room = 1;

        $('#add-more').click(function () {

            room++;
            var objTo = document.getElementById('education_fields')
            var divtest = document.createElement("div");
            divtest.setAttribute("class", "form-group removeclass" + room);
            var rdiv = 'removeclass' + room;
            var placeholderText = '@lang('menu.skills') @lang('app.name')';
            divtest.innerHTML = '<div class="flex gap-2 removeclass' + room + '"><div class="flex-1"><div class="mb-4"><div class="flex"> <input type="text" name="name[]" class="w-full px-3 py-2 border border-gray-300 rounded-l-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary rounded-r-none" placeholder="' + placeholderText + '"><button class="px-4 py-2 bg-red-600 text-white rounded-r-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 rounded-l-none" type="button" onclick="remove_education_fields(' + room + ');"> <i class="fa fa-minus"></i> </button></div></div></div></div>';

            objTo.appendChild(divtest)
        })

        function remove_education_fields(rid) {
            $('.removeclass' + rid).remove();
        }

        $('#save-form').click(function () {
            $.easyAjax({
                url: '{{route('admin.skills.update', $skill->id)}}',
                container: '#createForm',
                type: "POST",
                redirect: true,
                data: $('#createForm').serialize()
            })
        });
    </script>
@endpush