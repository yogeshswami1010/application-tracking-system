@extends('layouts.app')

@push('head-script')
    @include('admin.partials.settings-design-styles')
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/switchery/dist/switchery.min.css') }}">
@endpush

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('menu.linkedInSettings')</span>
@endsection

@section('page-subtitle')
    @lang('modules.linkedInSettings.client_id') · @lang('modules.linkedInSettings.callback_url')
@endsection

@section('content')
    <div class="mx-auto pb-10">
        <form id="editSettings" class="ajax-form space-y-4">
            @csrf
            @method('PUT')

            <div class="bs-set-card mb-4">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="#2563EB" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('modules.linkedInSettings.status')</h2>
                        <p class="text-[12px] text-[#8892A0]">OAuth</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="switchery-demo">
                        <input id="status" type="checkbox" name="status" @if ($linkedInSetting->status == 'enable') checked @endif value="enable" class="js-switch change-language-setting" data-color="#2563EB" data-size="small" onchange="toggle('#linkedin-credentials');">
                    </div>
                </div>
            </div>

            <div id="linkedin-credentials" class="bs-set-card mb-4">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic" style="background:#0A66C2;">
                        <span class="text-[11px] font-bold text-white">in</span>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('modules.linkedInSettings.client_id')</h2>
                        <p class="text-[12px] text-[#8892A0]">@lang('modules.linkedInSettings.client_secret')</p>
                    </div>
                </div>
                <div class="space-y-4 p-6">
                    <div>
                        <label for="client_id" class="bs-set-lbl">@lang('modules.linkedInSettings.client_id')</label>
                        <input type="text" class="bs-f-input" id="client_id" name="client_id" value="{{ $linkedInSetting->client_id }}">
                    </div>
                    <div>
                        <label for="client_secret" class="bs-set-lbl">@lang('modules.linkedInSettings.client_secret')</label>
                        <input type="password" class="bs-f-input" id="client_secret" name="client_secret" value="{{ $linkedInSetting->client_secret }}">
                    </div>
                    <div>
                        <label for="callback_url" class="bs-set-lbl">@lang('modules.linkedInSettings.callback_url')</label>
                        <input type="text" class="bs-f-input bs-readonly" readonly id="callback_url" name="callback_url" value="{{ $linkedInSetting->callback_url }}">
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="button" id="save-form" class="inline-flex items-center justify-center gap-2 rounded-[11px] bg-[#2563EB] px-8 py-3 text-[13.5px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @lang('app.save')
                </button>
                <button type="reset" class="rounded-[11px] border-[1.5px] border-[#E2DED8] bg-[#F1F3F7] px-6 py-3 text-[13.5px] font-semibold text-[#5A6478] transition hover:border-[#C4CBD4] hover:bg-[#E8EBEF]">@lang('app.reset')</button>
            </div>
        </form>
    </div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/node_modules_files/switchery/dist/switchery.min.js') }}"></script>
    <script>
        function toggle(elementBox) {
            $(elementBox).slideToggle();
        }

        $('#status').is(':checked') ? $('#linkedin-credentials').show() : $('#linkedin-credentials').hide();

        $('.js-switch').each(function () {
            new Switchery($(this)[0], $(this).data());
        });

        $('#save-form').click(function () {
            $.easyAjax({
                url: '{{ route('admin.linkedin-settings.update', $linkedInSetting->id) }}',
                container: '#editSettings',
                type: 'POST',
                redirect: true,
                messagePosition: 'inline',
                data: $('#editSettings').serialize(),
            });
        });
    </script>
@endpush
