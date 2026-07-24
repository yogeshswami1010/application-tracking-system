@extends('layouts.app')

@push('head-script')
    @include('admin.partials.settings-design-styles')
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/switchery/dist/switchery.min.css') }}">
@endpush

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('menu.smsSettings')</span>
@endsection

@section('page-subtitle')
    @lang('app.sms.nexmoCredential')
@endsection

@section('content')
    <div class="mx-auto pb-10">
        <form id="editSmsSettings" class="ajax-form space-y-4">
            <div id="alert"></div>
            @csrf
            @method('PUT')

            <div class="bs-set-card mb-4">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="#2563EB" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">SMS Status</h2>
                        <p class="text-[12px] text-[#8892A0]">Enable applicant text messages</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="switchery-demo max-w-xs">
                        <input id="nexmo_status" name="nexmo_status" type="checkbox" @if ($credentials->nexmo_status == 'active') checked @endif value="active" class="js-switch change-language-setting" data-color="#2563EB" data-size="small" data-setting-id="{{ $credentials->id }}" onchange="showProviderCredentials();">
                    </div>
                </div>
            </div>

            <div class="bs-set-card mb-4">
                <div class="space-y-4 p-6">
                    <div>
                        <label for="sms_provider" class="bs-set-lbl">SMS Provider</label>
                        <select name="sms_provider" id="sms_provider" class="bs-f-input">
                            <option value="telnyx" @selected(($credentials->sms_provider ?? 'vonage') === 'telnyx')>Telnyx</option>
                            <option value="vonage" @selected(($credentials->sms_provider ?? 'vonage') === 'vonage')>Vonage / Nexmo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="nexmo-credentials" class="bs-set-card mb-4">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic" style="background:#ECFDF5;">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="#059669" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('app.sms.nexmoCredential')</h2>
                        <p class="text-[12px] text-[#8892A0]">API key · Secret · From</p>
                    </div>
                </div>
                <div class="space-y-4 p-6">
                    <div>
                        <label for="nexmo_key" class="bs-set-lbl">@lang('app.sms.nexmoKey')</label>
                        <input type="text" name="nexmo_key" id="nexmo_key" class="bs-f-input" value="{{ $credentials->nexmo_key }}">
                    </div>
                    <div>
                        <label for="nexmo_secret" class="bs-set-lbl">@lang('app.sms.nexmoSecret')</label>
                        <input type="password" name="nexmo_secret" id="nexmo_secret" class="bs-f-input" value="{{ $credentials->nexmo_secret }}">
                    </div>
                    <div>
                        <label for="nexmo_from" class="bs-set-lbl">@lang('app.sms.nexmoFrom')</label>
                        <input type="text" name="nexmo_from" id="nexmo_from" class="bs-f-input" value="{{ $credentials->nexmo_from }}">
                    </div>
                </div>
            </div>

            <div id="telnyx-credentials" class="bs-set-card mb-4">
                <div class="bs-set-card-hd">
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">Telnyx Credentials</h2>
                        <p class="text-[12px] text-[#8892A0]">API key and SMS-enabled sender number</p>
                    </div>
                </div>
                <div class="space-y-4 p-6">
                    <div>
                        <label for="telnyx_api_key" class="bs-set-lbl">Telnyx API Key</label>
                        <input type="password" name="telnyx_api_key" id="telnyx_api_key" class="bs-f-input" value="{{ $credentials->telnyx_api_key }}" autocomplete="new-password">
                    </div>
                    <div>
                        <label for="telnyx_from_number" class="bs-set-lbl">Telnyx Sender Number</label>
                        <input type="text" name="telnyx_from_number" id="telnyx_from_number" class="bs-f-input" value="{{ $credentials->telnyx_from_number }}" placeholder="+14165551234">
                        <p class="mt-1 text-[11px] text-[#8892A0]">Use the SMS-enabled number assigned to your Telnyx Messaging Profile.</p>
                    </div>
                    <div>
                        <label for="telnyx_public_key" class="bs-set-lbl">Telnyx Webhook Public Key</label>
                        <input type="text" name="telnyx_public_key" id="telnyx_public_key" class="bs-f-input" value="{{ $credentials->telnyx_public_key }}" autocomplete="off">
                        <p class="mt-1 text-[11px] text-[#8892A0]">Copy the public key from your Telnyx Mission Control Portal. Incoming replies are accepted only when their signature is valid.</p>
                    </div>
                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700">Inbound webhook URL</p>
                        <p class="mt-1 break-all text-[12px] text-blue-900">{{ route('telnyx-webhook') }}</p>
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

        function showProviderCredentials() {
            var provider = $('#sms_provider').val();
            var enabled = $('#nexmo_status').is(':checked');
            $('#nexmo-credentials').toggle(enabled && provider === 'vonage');
            $('#telnyx-credentials').toggle(enabled && provider === 'telnyx');
        }
        $('#sms_provider').on('change', showProviderCredentials);
        showProviderCredentials();

        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function () {
            new Switchery($(this)[0], $(this).data());
        });

        $('#save-form').click(function () {
            $.easyAjax({
                url: '{{ route('admin.sms-settings.update', $credentials->id) }}',
                container: '#editSmsSettings',
                type: 'POST',
                redirect: true,
                messagePosition: 'inline',
                data: $('#editSmsSettings').serialize(),
            });
        });
    </script>
@endpush
