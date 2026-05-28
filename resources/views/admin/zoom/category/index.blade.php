@extends('layouts.app')
@push('head-script')
<style>
    .d-none {
        display: none;
    }
</style>
@endpush
@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang($pageTitle)</span>
@endsection

@section('page-subtitle')
    @lang('app.addNew')
@endsection
@section('content')

<div class="flex flex-col">
    <div class="w-full">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">@lang('zoom::modules.zoomsetting.setting')</h4>

                <form class="ajax-form space-y-4" method="POST" id="createzoom">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1 required">@lang('zoom::modules.zoomsetting.zoomapikey')</label>
                                <div class="relative">
                                    <input type="password" name="api_key" id="api_key" value="{{$zoom->api_key ?? ''}}" class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 fa fa-fw fa-eye field-icon toggle-password cursor-pointer"></span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1 required">@lang('zoom::modules.zoomsetting.zoomapisecret')</label>
                                <div class="relative">
                                    <input type="password" name="secret_key" id="secret_key" value="{{$zoom->secret_key ?? ''}}" class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 fa fa-fw fa-eye field-icon toggle-password cursor-pointer"></span>
                                </div>
                            </div>
                        </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('zoom::modules.zoomsetting.openZoomApp')</label>
                                <div class="flex gap-4 mt-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="meeting_app" @if($zoom->meeting_app == 'zoom_app') checked @endif id="zoom_app" value="zoom_app" class="mr-2">
                                        <span>@lang('app.yes')</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="meeting_app" @if($zoom->meeting_app == 'in_app') checked @endif id="in_app" value="in_app" class="mr-2">
                                        <span>@lang('app.no')</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="mail_from_name" class="block text-sm font-medium text-gray-700 mb-1">@lang('app.webhook')</label>
                            <p class="font-bold">{{ route('zoom-webhook') }}</p>
                            <p class="text-blue-600 text-sm">(@lang('zoom::modules.zoomsetting.webhookInfo'))</p>
                        </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="update-form" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"> <i class="fa fa-check"></i> @lang('app.update')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer-script')
<script>
    $('#update-form').click(function() {
        var url = "{{route('admin.zoom-setting.update', $zoom->id)}}";

        $.easyAjax({
            url: url,
            container: '#createzoom',
            type: "POST",
            redirect: true,
            data: $('#createzoom').serialize()
        })
    });
</script>

@endpush