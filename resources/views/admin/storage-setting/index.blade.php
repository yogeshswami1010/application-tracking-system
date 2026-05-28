@extends('layouts.app')

@push('head-script')
    @include('admin.partials.settings-design-styles')
    <link href="{{ asset('assets/node_modules_files/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .storage-settings-page .select2-container {
            width: 100% !important;
        }

        .storage-settings-page .select2-container--default .select2-selection--single {
            height: 44px;
            border: 1.5px solid #E2DED8;
            border-radius: 11px;
            background-color: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .storage-settings-page .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 41px;
            color: #1A1E2E;
            font-size: 13.5px;
            padding-left: 14px;
            padding-right: 34px;
        }

        .storage-settings-page .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
            right: 10px;
        }

        .storage-settings-page .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #9CA3AF transparent transparent transparent;
            border-width: 5px 4px 0 4px;
        }

        .storage-settings-page .select2-container--default.select2-container--open .select2-selection--single,
        .storage-settings-page .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.09);
        }

        .storage-settings-page .select2-dropdown {
            border: 1px solid #E2DED8;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(26, 30, 46, 0.12);
        }

        .storage-settings-page .select2-results__option {
            font-size: 13px;
            padding: 9px 12px;
        }

        .storage-settings-page .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: #2563EB;
        }
    </style>
@endpush

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('menu.storageSetting')</span>
@endsection

@section('page-subtitle')
    @lang('menu.selectStorage') · @lang('menu.local') · @lang('menu.awsS3') · @lang('menu.digitalocean') · @lang('menu.wasabi') · @lang('menu.minio')
@endsection

@section('content')
    <div class="storage-settings-page mx-auto pb-10">
        <div class="bs-set-card mb-4">
            <div class="bs-set-card-hd">
                <div class="bs-set-card-ic" style="background:#EFF6FF;">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="#2563EB" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('menu.selectStorage')</h2>
                    <p class="text-[12px] text-[#8892A0]">@lang('menu.storageLocalNote') @lang('menu.storageCloudNote')</p>
                </div>
            </div>
        </div>

        <form id="StorageSetting" class="ajax-form space-y-4" method="POST">
            @csrf

            <div class="bs-set-card mb-4">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="#2563EB" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('menu.selectStorage')</h2>
                        <p class="text-[12px] text-[#8892A0]">@lang('menu.local') · @lang('menu.awsS3') · @lang('menu.digitalocean') · @lang('menu.wasabi') · @lang('menu.minio')</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
                        @foreach ([
                            'local' => ['label' => __('menu.local')],
                            'digitalocean' => ['label' => __('menu.digitalocean'), 'doc' => 'https://docs.digitalocean.com/products/spaces/'],
                            'aws_s3' => ['label' => __('menu.awsS3'), 'doc' => 'https://aws.amazon.com/s3/'],
                            'wasabi' => ['label' => __('menu.wasabi'), 'doc' => 'https://wasabi.com/'],
                            'minio' => ['label' => __('menu.minio'), 'doc' => 'https://min.io/docs/minio/linux/index.html'],
                        ] as $value => $meta)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="storage" value="{{ $value }}" class="peer sr-only storage-radio"
                                    {{ ($selected_storage ?? 'local') === $value ? 'checked' : '' }}>
                                <div class="rounded-[11px] border-2 border-[#E2DED8] p-4 transition-all peer-checked:border-[#2563EB] peer-checked:bg-[#EFF6FF] hover:border-[#CBD5E1]">
                                    <div class="flex flex-col items-center gap-2 text-center">
                                        @if (!empty($meta['doc']))
                                            <div class="flex w-full justify-end">
                                                <a href="{{ $meta['doc'] }}" target="_blank" rel="noopener" class="text-[11px] font-semibold text-[#2563EB] hover:underline" onclick="event.stopPropagation()">Docs</a>
                                            </div>
                                        @else
                                            <div class="h-5 w-full"></div>
                                        @endif
                                        <span class="text-[13px] font-bold text-[#1A1E2E]">{{ $meta['label'] }}</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div id="credentials-digitalocean" class="storage-cred bs-set-card mb-4 {{ ($selected_storage ?? '') === 'digitalocean' ? '' : 'hidden' }}">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic" style="background:#E0F2FE;">
                        <svg class="h-[18px] w-[18px]" fill="#0080FF" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2L4 6v12l8 4 8-4V6l-8-4zm0 2.2l5.5 2.75L12 9.7 6.5 6.95 12 4.2zM6 8.9l6 3v7.35l-6-3V8.9zm8 10.35v-7.35l6-3v7.35l-6 3z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('menu.digitalocean')</h2>
                        <p class="text-[12px] text-[#8892A0]">Access key · Secret · Bucket · Region</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                    <div class="form-group">
                        <label class="bs-set-lbl">Access key</label>
                        <input type="text" name="digitalocean_key" class="bs-f-input" value="{{ $digitalocean_key }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Secret key</label>
                        <input type="password" name="digitalocean_secret_key" class="bs-f-input" value="{{ $digitalocean_secret_key }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Bucket</label>
                        <input type="text" name="digitalocean_bucket" class="bs-f-input" value="{{ $digitalocean_bucket }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Region</label>
                        <select name="digitalocean_region" class="bs-f-sel select2 w-full">
                            @foreach (\App\StorageSetting::DIGITALOCEAN_REGIONS as $key => $label)
                                <option value="{{ $key }}" @selected(($digitalocean_region ?? '') === $key)>{{ $label }} — {{ $key }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div id="credentials-aws_s3" class="storage-cred bs-set-card mb-4 {{ ($selected_storage ?? '') === 'aws_s3' ? '' : 'hidden' }}">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic" style="background:#FFF7ED;">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="#F97316" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('menu.awsS3')</h2>
                        <p class="text-[12px] text-[#8892A0]">Access key · Secret · Region · Bucket</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                    <div class="form-group">
                        <label class="bs-set-lbl">Access key</label>
                        <input type="text" name="aws_access_key" class="bs-f-input" value="{{ $aws_access_key }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Secret key</label>
                        <input type="password" name="aws_secret_key" class="bs-f-input" value="{{ $aws_secret_key }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Region</label>
                        <select name="aws_region" class="bs-f-sel select2 w-full">
                            @foreach (\App\StorageSetting::AWS_REGIONS as $key => $region)
                                <option value="{{ $key }}" @selected(($aws_region ?? '') === $key)>{{ $region }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Bucket</label>
                        <input type="text" name="aws_bucket" class="bs-f-input" value="{{ $aws_bucket }}" autocomplete="off">
                    </div>
                </div>
            </div>

            <div id="credentials-wasabi" class="storage-cred bs-set-card mb-4 {{ ($selected_storage ?? '') === 'wasabi' ? '' : 'hidden' }}">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic" style="background:#ECFDF5;">
                        <svg class="h-[18px] w-[18px]" fill="#00CE3E" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2L4 6v12l8 4 8-4V6l-8-4zm-1 7h2v10h-2V9zm1 14a2 2 0 110-4 2 2 0 010 4z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('menu.wasabi')</h2>
                        <p class="text-[12px] text-[#8892A0]">Access key · Secret · Bucket · Region</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                    <div class="form-group">
                        <label class="bs-set-lbl">Access key</label>
                        <input type="text" name="wasabi_access_key" class="bs-f-input" value="{{ $wasabi_access_key }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Secret key</label>
                        <input type="password" name="wasabi_secret_key" class="bs-f-input" value="{{ $wasabi_secret_key }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Bucket</label>
                        <input type="text" name="wasabi_bucket" class="bs-f-input" value="{{ $wasabi_bucket }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Region</label>
                        <select name="wasabi_region" class="bs-f-sel select2 w-full">
                            @foreach (\App\StorageSetting::WASABI_REGIONS as $key => $label)
                                <option value="{{ $key }}" @selected(($wasabi_region ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div id="credentials-minio" class="storage-cred bs-set-card mb-4 {{ ($selected_storage ?? '') === 'minio' ? '' : 'hidden' }}">
                <div class="bs-set-card-hd">
                    <div class="bs-set-card-ic" style="background:#FEF2F2;">
                        <svg class="h-[18px] w-[18px]" fill="#C72C48" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4L5 7v10l7 3 7-3V7l-7-3zm0 2l5 2.25L12 10.5 7 8.25 12 6zM7 9.5l5 2.25v6.5L7 16.25v-6.75zm6 8.75v-6.5l5-2.25v6.75l-5 2.25z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('menu.minio')</h2>
                        <p class="text-[12px] text-[#8892A0]">Endpoint · Access key · Secret · Bucket · Region</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                    <div class="form-group md:col-span-2">
                        <label class="bs-set-lbl">Endpoint URL</label>
                        <input type="text" name="minio_endpoint" class="bs-f-input" value="{{ $minio_endpoint }}" placeholder="https://minio.example.com:9000" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Access key</label>
                        <input type="text" name="minio_access_key" class="bs-f-input" value="{{ $minio_access_key }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Secret key</label>
                        <input type="password" name="minio_secret_key" class="bs-f-input" value="{{ $minio_secret_key }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Bucket</label>
                        <input type="text" name="minio_bucket" class="bs-f-input" value="{{ $minio_bucket }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="bs-set-lbl">Region</label>
                        <input type="text" name="minio_region" class="bs-f-input" value="{{ $minio_region }}" placeholder="us-east-1" autocomplete="off">
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="button" id="save-form" class="inline-flex items-center justify-center gap-2 rounded-[11px] bg-[#2563EB] px-8 py-3 text-[13.5px] font-bold text-white shadow-sm transition hover:bg-[#1d4ed8]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @lang('app.save')
                </button>
                <button type="button" id="test-storage" class="inline-flex items-center justify-center gap-2 rounded-[11px] border-[1.5px] border-[#2563EB] bg-[#EFF6FF] px-6 py-3 text-[13.5px] font-semibold text-[#2563EB] transition hover:bg-[#DBEAFE] {{ ($selected_storage ?? 'local') === 'local' ? 'hidden' : '' }}">
                    @lang('menu.testStorageConnection')
                </button>
            </div>
        </form>
    </div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script>
        function storageTogglePanels() {
            var v = $('input[name=storage]:checked').val() || 'local';
            $('.storage-cred').addClass('hidden');
            $('#credentials-' + v).removeClass('hidden');
            if (v === 'local') {
                $('#test-storage').addClass('hidden');
            } else {
                $('#test-storage').removeClass('hidden');
            }
        }
        $(function () {
            $('.select2').select2({
                width: '100%',
                dropdownAutoWidth: true
            });
            $('input[name=storage]').on('change', storageTogglePanels);
            storageTogglePanels();
        });

        $('#save-form').click(function () {
            $.easyAjax({
                url: '{{ route('admin.storage-settings.store') }}',
                container: '#StorageSetting',
                type: 'POST',
                redirect: true,
                data: $('#StorageSetting').serialize(),
                success: function () {},
            });
        });

        $('#test-storage').click(function () {
            $.easyAjax({
                url: '{{ route('admin.storage-settings.test-connection') }}',
                container: '#StorageSetting',
                type: 'POST',
                data: $('#StorageSetting').serialize(),
                success: function () {},
            });
        });
    </script>
@endpush
