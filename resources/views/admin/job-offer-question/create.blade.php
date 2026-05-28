@extends('layouts.app')

@section('content')

    <div class="flex flex-col">
        <div class="w-full">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">@lang('app.createNew')</h4>

                    <form class="ajax-form space-y-4" method="POST" id="createForm">
                        @csrf

                        <div class="form-group">
                            <label for="question" class="form-label required">@lang('menu.question')</label>
                            <input type="text" name="question" class="form-control" placeholder="@lang('menu.question')" id="question">
                        </div>
                        <div class="form-group">
                            <label for="required" class="form-label">@lang('app.required')</label>
                            <select name="required" class="form-control" id="required">
                                <option value="yes">@lang('app.yes')</option>
                                <option value="no">@lang('app.no')</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="type" class="form-label">@lang('app.type')</label>
                            <select name="type" class="form-control" id="type">
                                <option value="text">@lang('app.text')</option>
                                <option value="file">@lang('app.file')</option>
                            </select>
                        </div>

                        <button type="button" id="save-form" class="btn btn-success"><i class="fa fa-check"></i> @lang('app.save')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
<script>
    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route('admin.job-onboard-questions.store')}}',
            container: '#createForm',
            type: "POST",
            redirect: true,
            data: $('#createForm').serialize()
        })
    });
</script>
@endpush