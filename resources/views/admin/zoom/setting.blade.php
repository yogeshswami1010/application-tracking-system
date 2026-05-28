@extends('layouts.app')

@push('head-script')
    @include('admin.partials.settings-design-styles')
@endpush

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('menu.zoomSetting')</span>
@endsection

@section('page-subtitle')
    @lang('modules.zoomsetting.enableSetting') · API · @lang('app.webhook')
@endsection

@section('content')
    <div class="mx-auto pb-10">
        <div class="bs-set-card">
            <div class="bs-set-card-hd">
                <div class="bs-set-card-ic" style="background:#0B5CFF;">
                    <svg class="h-[18px] w-[18px] text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3.5" y="7.5" width="11" height="9" rx="2.2" stroke-width="1.8"></rect>
                        <path d="M14.5 10.4l4.3-2.4c.67-.37 1.5.11 1.5.88v6.22c0 .77-.83 1.25-1.5.88l-4.3-2.4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-[15px] font-bold text-[#1A1E2E]">Zoom</h2>
                    <p class="text-[12px] text-[#8892A0]">@lang('modules.zoomsetting.accountId')</p>
                </div>
            </div>
            <div class="p-6">
                <form class="ajax-form space-y-4" method="POST" id="createzoom">
                    @csrf
                    @method('PUT')

                    <div class="mb-4 rounded-[11px] border border-[#F0EEE9] bg-[#FAFAF8] px-4 py-3">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[13px] font-semibold text-[#3D4A5C] required">@lang('modules.zoomsetting.enableSetting')</p>
                                <p class="text-[11px] text-[#8892A0]">@lang('app.enable') / @lang('app.disable')</p>
                            </div>
                            <label for="enable_zoom_toggle" class="relative inline-flex cursor-pointer items-center">
                                <input type="hidden" name="enable_zoom" id="enable_zoom" value="{{ $zoom->enable_zoom == 1 ? 1 : 0 }}">
                                <input type="checkbox" id="enable_zoom_toggle" class="peer sr-only" {{ $zoom->enable_zoom == 1 ? 'checked' : '' }}>
                                <span class="bs-toggle-track">
                                    <span class="bs-toggle-thumb"></span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="bs-set-lbl required" for="account_id">@lang('modules.zoomsetting.accountId')</label>
                            <input type="text" name="account_id" id="account_id" value="{{ $zoom->account_id ?? '' }}" class="bs-f-input">
                        </div>
                        <div>
                            <label class="bs-set-lbl required" for="api_key">@lang('modules.zoomsetting.zoomapikey')</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 0h12a2 2 0 002-2v-5a2 2 0 00-2-2h-1V6a5 5 0 00-10 0v2H6a2 2 0 00-2 2v5a2 2 0 002 2z"></path>
                                    </svg>
                                </span>
                                <input type="password" name="api_key" id="api_key" value="{{ $zoom->api_key ?? '' }}" class="bs-f-input pl-10 pr-11">
                                <span class="toggle-password fa fa-eye absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-[#94A3B8] transition hover:text-[#2563EB]"></span>
                            </div>
                        </div>
                        <div>
                            <label class="bs-set-lbl required" for="secret_key">@lang('modules.zoomsetting.zoomapisecret')</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 0h12a2 2 0 002-2v-5a2 2 0 00-2-2h-1V6a5 5 0 00-10 0v2H6a2 2 0 00-2 2v5a2 2 0 002 2z"></path>
                                    </svg>
                                </span>
                                <input type="password" name="secret_key" id="secret_key" value="{{ $zoom->secret_key ?? '' }}" class="bs-f-input pl-10 pr-11">
                                <span class="toggle-password fa fa-eye absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-[#94A3B8] transition hover:text-[#2563EB]"></span>
                            </div>
                        </div>
                        <div>
                            <label class="bs-set-lbl required" for="secret_token">@lang('modules.zoomsetting.secretToken')</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 0h12a2 2 0 002-2v-5a2 2 0 00-2-2h-1V6a5 5 0 00-10 0v2H6a2 2 0 00-2 2v5a2 2 0 002 2z"></path>
                                    </svg>
                                </span>
                                <input type="password" name="secret_token" id="secret_token" value="{{ $zoom->secret_token ?? '' }}" class="bs-f-input pl-10 pr-11">
                                <span class="toggle-password fa fa-eye absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-[#94A3B8] transition hover:text-[#2563EB]"></span>
                            </div>
                        </div>
                        <div>
                            <label class="bs-set-lbl required" for="meeting_sdk_api_key">@lang('modules.zoomsetting.meetingsdkapikey')</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 0h12a2 2 0 002-2v-5a2 2 0 00-2-2h-1V6a5 5 0 00-10 0v2H6a2 2 0 00-2 2v5a2 2 0 002 2z"></path>
                                    </svg>
                                </span>
                                <input type="password" name="meeting_sdk_api_key" id="meeting_sdk_api_key" value="{{ $zoom->meeting_sdk_api_key ?? '' }}" class="bs-f-input pl-10 pr-11">
                                <span class="toggle-password fa fa-eye absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-[#94A3B8] transition hover:text-[#2563EB]"></span>
                            </div>
                        </div>
                        <div>
                            <label class="bs-set-lbl required" for="meeting_sdk_api_secret">@lang('modules.zoomsetting.meetingSdkApiSecret')</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 0h12a2 2 0 002-2v-5a2 2 0 00-2-2h-1V6a5 5 0 00-10 0v2H6a2 2 0 00-2 2v5a2 2 0 002 2z"></path>
                                    </svg>
                                </span>
                                <input type="password" name="meeting_sdk_api_secret" id="meeting_sdk_api_secret" value="{{ $zoom->meeting_sdk_api_secret ?? '' }}" class="bs-f-input pl-10 pr-11">
                                <span class="toggle-password fa fa-eye absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-[#94A3B8] transition hover:text-[#2563EB]"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="bs-set-lbl">@lang('modules.zoomsetting.openZoomApp')</label>
                        <div class="mt-2 flex flex-wrap gap-6">
                            <label class="flex cursor-pointer items-center gap-2 text-[13px] font-medium text-[#3D4A5C]">
                                <input type="radio" name="meeting_app" @if ($zoom->meeting_app == 'zoom_app') checked @endif id="zoom_app" value="zoom_app" class="h-4 w-4 border-[#E2DED8] text-[#2563EB] focus:ring-[#2563EB]">
                                @lang('app.yes')
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 text-[13px] font-medium text-[#3D4A5C]">
                                <input type="radio" name="meeting_app" @if ($zoom->meeting_app == 'in_app') checked @endif id="in_app" value="in_app" class="h-4 w-4 border-[#E2DED8] text-[#2563EB] focus:ring-[#2563EB]">
                                @lang('app.no')
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 rounded-[11px] border border-[#F0EEE9] bg-[#FAFAF8] p-4">
                        <label class="bs-set-lbl">@lang('app.webhook')</label>
                        <p class="font-mono text-[13px] font-semibold text-[#1A1E2E]">{{ route('zoom-webhook') }}</p>
                        <p class="mt-1 text-[12px] text-[#2563EB]">(@lang('modules.zoomsetting.webhookInfo'))</p>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button type="button" id="update-form" class="inline-flex items-center justify-center gap-2 rounded-[11px] bg-[#2563EB] px-8 py-3 text-[13.5px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8]">
                            <i class="fa fa-check"></i>
                            @lang('app.update')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
    <script>
        function syncZoomToggleValue() {
            $('#enable_zoom').val($('#enable_zoom_toggle').is(':checked') ? 1 : 0);
        }

        syncZoomToggleValue();

        $('#enable_zoom_toggle').on('change', function () {
            syncZoomToggleValue();
        });

        $('#update-form').click(function () {
            var url = '{{ route('admin.zoom-setting.update', $zoom->id) }}';
            $.easyAjax({
                url: url,
                container: '#createzoom',
                type: 'POST',
                redirect: true,
                data: $('#createzoom').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        location.reload();
                    }
                },
            });
        });

        $('.toggle-password').click(function () {
            var inp = $(this).siblings('input');
            inp.attr('type', inp.attr('type') === 'password' ? 'text' : 'password');
            $(this).toggleClass('fa-eye fa-eye-slash');
        });
    </script>
@endpush
