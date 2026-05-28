@extends('layouts.app')
@push('head-script')
    @include('admin.partials.settings-design-styles')
    <link rel="stylesheet" href="{{ asset('assets/node_modules_files/switchery/dist/switchery.min.css') }}">
    <style>
        .dropify-wrapper,
        .dropify-preview,
        .dropify-render img {
            background-color: var(--sidebar-bg) !important;
        }

        #carousel-image-gallery .card .img-holder {
            height: 150px;
            overflow: hidden;
        }

        #carousel-image-gallery .card .img-holder img {
            height: 100%;
            object-fit: cover;
            object-position: top;
        }

        .note-group-select-from-files {
            display: none;
        }

        #captcha-detail-modal .modal-dialog {
            height: 90%;
            max-width: 100%;
        }

        #captcha-detail-modal .modal-content {
            width: 600px;
            margin: 0 auto;
        }

        .modal.show {
            padding-right: 0px !important;
        }

        #v2_captcha_container {
            margin-bottom: 1%;
        }

        #save-btn-div {
            margin-top: 2%;
        }

        #customCss {
            margin-left: 0.4%;
            margin-right: 0.4%;
        }

        .google_recaptcha_options label {
            margin-bottom: -0.5rem;
        }
    </style>
@endpush

@section('page-title-html')
    <span class="text-[22px] font-bold tracking-tight text-[#1A1E2E]">@lang('menu.securitySettings')</span>
@endsection

@section('page-subtitle')
    @lang('app.googleRecaptcha') · v2 / v3
@endsection

@section('content')
    <div class="mx-auto pb-10">
        <div class="bs-set-card">
            <div class="bs-set-card-hd">
                <div class="bs-set-card-ic" style="background:#FEF3C7;">
                    <svg class="h-[18px] w-[18px]" fill="none" stroke="#D97706" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h2 class="text-[15px] font-bold text-[#1A1E2E]">@lang('app.googleRecaptchaStatus')</h2>
                    <p class="text-[12px] text-[#8892A0]">@lang('app.googleRecaptchaCredential')</p>
                </div>
            </div>
            <div class="p-6">
                <form id="google-captcha-setting-form" class="ajax-form space-y-4" method="POST">
                    <div id="alert"></div>
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="googleRecaptcha" class="bs-set-lbl">@lang('app.googleRecaptcha')</label>
                        <div class="switchery-demo max-w-xs">
                            <input id="googleRecaptcha" name="status" type="checkbox" @if ($credentials->status == 'active') checked @endif value="active" class="js-switch change-language-setting" data-color="#2563EB" data-size="small" data-setting-id="{{ $credentials->id }}" onchange="changeStatus();">
                        </div>
                    </div>

                    <div id="google-captcha-credentials">
                        <div class="mb-4">
                            <label class="bs-set-lbl">@lang('app.choosegooglerecaptchaversion:')</label>
                            <div class="mt-2 flex flex-wrap gap-6">
                                <label class="flex cursor-pointer items-center gap-2 text-[13px] font-medium text-[#3D4A5C]">
                                    <input value="v2" id="v2" type="radio" name="version" @if ($credentials->v2_status == 'active') checked @endif class="h-4 w-4 border-[#E2DED8] text-[#2563EB] focus:ring-[#2563EB]">
                                    v2
                                </label>
                                <label class="flex cursor-pointer items-center gap-2 text-[13px] font-medium text-[#3D4A5C]">
                                    <input value="v3" id="v3" type="radio" name="version" @if ($credentials->v3_status == 'active') checked @endif class="h-4 w-4 border-[#E2DED8] text-[#2563EB] focus:ring-[#2563EB]">
                                    v3
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="bs-set-lbl">
                                @lang('app.checkoptionswhereyouwantstoapply')
                            </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                    <div class="google_recaptcha_options">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="job_apply_page" id="job_apply_page"
                                                @if ($credentials->job_apply_page == 'active') checked @endif
                                                value="active" class="js-switch change-language-setting mr-2" data-color="#2563EB"
                                                data-size="small" data-setting-id="{{ $credentials->id }}">
                                            <span>@lang('app.jobapplypage')</span>
                                        </label>
                                    </div>
                                    <div class="google_recaptcha_options">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="login_page" id="login_page"
                                                @if ($credentials->login_page == 'active') checked @endif value="active"
                                                class="js-switch change-language-setting mr-2" data-color="#2563EB"
                                                data-size="small" data-setting-id="{{ $credentials->id }}">
                                            <span>@lang('app.login_page')</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div id="google_captcha_v3">
                                <div class="mb-4">
                                    <label class="bs-set-lbl">@lang('app.site_key')</label>
                                    <input type="text" name="v3_site_key" id="v3_google_captcha_site_key" class="bs-f-input" value="{{ $credentials->v3_site_key }}">
                                </div>

                                <div class="mb-4">
                                    <label class="bs-set-lbl">@lang('app.secret_key')</label>
                                    <input type="text" name="v3_secret_key" id="v3_google_captcha_secret" class="bs-f-input" value="{{ $credentials->v3_secret_key }}">
                                </div>

                                <div class="mb-4">
                                    <button type="button" class="inline-flex items-center gap-2 rounded-[11px] bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#1d4ed8]" id="verify-v3">
                                        <i class="fa fa-check"></i> @lang('app.verify')
                                    </button>
                                </div>
                                <div class="w-full" id="v3_captcha_container"></div>

                            </div>

                            <div id="google_captcha_v2">
                                <div class="mb-4">
                                    <label class="bs-set-lbl">@lang('app.site_key')</label>
                                    <input type="text" name="v2_site_key" id="v2_google_captcha_site_key" class="bs-f-input" value="{{ $credentials->v2_site_key }}">
                                </div>

                                <div class="mb-4">
                                    <label class="bs-set-lbl">@lang('app.secret_key')</label>
                                    <input type="text" name="v2_secret_key" id="v2_google_captcha_secret" class="bs-f-input" value="{{ $credentials->v2_secret_key }}">
                                </div>

                                <div class="w-full" id="v2_captcha_container"></div>

                                <div class="mb-4">
                                    <button type="button" class="inline-flex items-center gap-2 rounded-[11px] bg-[#2563EB] px-5 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#1d4ed8]" id="verify-v2">
                                        <i class="fa fa-check"></i> @lang('app.verify')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
            </div>
        </div>
    </div>
@endsection
@push('footer-script')
    <script src="{{ asset('assets/node_modules_files/switchery/dist/switchery.min.js') }}"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>

    <script>
        function changeStatus() {

            var url = "{{ route('admin.security-setting.update', $credentials->id) }}";
            var status = $('#googleRecaptcha').is(':checked') ? 'active' : 'inactive';
            var token = '{{ csrf_token() }}';
            $.easyAjax({
                url: url,
                type: "POST",
                data: {
                    'status': status,
                    'type': 'status',
                    '_method': "PUT",
                    '_token': token
                },
            })

            $('#google-captcha-credentials').toggle();
        }

        @if ($credentials->status == 'active')
            $('#google-captcha-credentials').show()
        @else
            $('#google-captcha-credentials').hide()
        @endif ;

        // Switchery
        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function() {
            new Switchery($(this)[0], $(this).data());

        });

        // Update Mail Setting
        function saveForm() {
            var url = "{{ route('admin.security-setting.update', $credentials->id) }}";

            $.easyAjax({
                url: url,
                container: '#google-captcha-setting-form',
                type: "POST",
                data: $('#google-captcha-setting-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        location.reload();
                    }
                }
            })
        }

        function errorMsg() {
            var form = $("#google-captcha-setting-form");
            var checkedValue = form.find("input[name=version]:checked").val();

            if (checkedValue == 'v3') {
                let msg = `<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded" role="alert"><i class="fa fa-info-circle"></i>
                Unexpected error occured.
                </div>`;
                $('#portlet-body').html(msg);
                $('#portlet-body').attr('data-error', true);
                $('#save-method').hide();
                return false;
            }

            swal({
                title: "Error..!",
                text: "@lang('errors.invalidReCaptcha')",
                icon: 'warning',
                showCancelButton: false,
                focusConfirm: false,
                confirmButtonText: "Ok",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false,
            }).then((willDelete) => {
                if (willDelete) {
                    location.reload();
                }
            });
        }

        $(function() {
            '{{ $credentials->v2_status }}' === 'active' ? $('#google_captcha_v2').show(): $('#google_captcha_v2')
                .hide();
            '{{ $credentials->v3_status }}' === 'active' ? $('#google_captcha_v3').show(): $('#google_captcha_v3')
                .hide();

            $('body').on('click', 'input[type="radio"]', function() {
                if ($(this).attr('id') == 'v2') {
                    $('#google_captcha_v2').show();
                    $('#google_captcha_v3').hide();
                } else {
                    $('#google_captcha_v3').show();
                    $('#google_captcha_v2').hide();
                }
            })

            $('input[type=radio][name=version]').change(function() {
                if (this.value == 'v2') {
                    $('#v2').removeClass('d-none');
                    $('#v3').addClass('d-none');
                } else if (this.value == 'v3') {
                    $('#v3').removeClass('d-none');
                    $('#v2').addClass('d-none');
                }
            });

            $(document).on('click', '#verify-v2', function() {

                let captchaContainerV2 = null;
                let key = $('#v2_google_captcha_site_key').val();
                let secret = $('#v2_google_captcha_secret').val();

                if (key === '' || secret === '') {
                    swal({
                        title: "Error..!",
                        icon: 'warning',
                        text: '@lang('errors.reCaptchaWarning')',
                    });
                    return false;
                }

                try {
                    captchaContainer = grecaptcha.render('v2_captcha_container', {
                        'sitekey': key,
                        'callback': function(response) {
                            if (response) {
                                saveForm();
                            }
                        },
                        'error-callback': function() {
                            errorMsg();
                        }
                    });
                } catch (error) {
                    errorMsg();
                }
            });

            $(document).on('click', '#verify-v3', function() {
                let key = $('#v3_google_captcha_site_key').val();
                let secret = $('#v3_google_captcha_secret').val();
                var url = "{{ route('admin.security-setting.verifyCaptcha') }}?key=" + key;
                if (key === '' || secret === '') {
                    swal({
                        title: "Error..!",
                        icon: 'warning',
                        text: '@lang('errors.reCaptchaWarning')',
                    });
                    return false;
                }
                var url = url;

                $('#modelHeading').html('@lang('app.package')');
                $('#application-lg-modal').modal('hide');
                $.ajaxModal('#application-lg-modal', url);
            });

        });
    </script>
@endpush
