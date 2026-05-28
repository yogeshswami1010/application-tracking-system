@extends('layouts.app')

@push('head-script')
<link rel="stylesheet" href="{{ asset('assets/node_modules_files/html5-editor/bootstrap-wysihtml5.css') }}">
@endpush


@section('content')
    <div class="flex flex-col">
        <div class="w-full">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <form id="storeSettings" class="ajax-form space-y-4">
                        @csrf
                        <div class="form-group">
                            <label class="required">@lang('app.name')</label>
                            <input type="text" class="form-control" id="name" name="name" value="">
                        </div>
                        <div class="form-group">
                            <label class="required">@lang('app.description')</label>
                            <textarea name="description" class="form-control" id="description" cols="30"
                                      rows="13"></textarea>
                        </div>
                        <input type="hidden" name="status" id="status" value="active" />
                        <div class="flex items-center gap-3">
                            <button type="button" id="save-form" class="btn btn-success">
                                @lang('app.save')
                            </button>
                            <button type="reset" class="btn btn-inverse">@lang('app.reset')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('footer-script')
<script src="{{ asset('assets/node_modules_files/html5-editor/wysihtml5-0.3.0.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/html5-editor/bootstrap-wysihtml5.js') }}"
        type="text/javascript"></script>
<script>
    $('#description').wysihtml5({
        "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
        "emphasis": true, //Italics, bold, etc. Default true
        "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
        "html": true, //Button which allows you to edit the generated HTML. Default false
        "link": true, //Button to insert a link. Default true
        "image": true, //Button to insert an image. Default true,
        "color": true, //Button to change color of font
        stylesheets: ["{{ asset('assets/node_modules_files/html5-editor/wysiwyg-color.css') }}"], // (path_to_project/lib/css/wysiwyg-color.css)
    });
    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route("admin.footer-settings.store")}}',
            container: '#storeSettings',
            type: "POST",
            redirect: true,
            file: true
        })
    });
</script>

@endpush