@extends('layouts.app')

@push('head-script')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datepicker/datepicker3.css') }}">
@endpush

@section('content')
    <div class="flex flex-col">
        <div class="w-full">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6">
                    <h4 class="text-xl font-semibold text-gray-900 mb-6">@lang('app.createNew')</h4>

                        <form class="ajax-form 747474" method="POST" id="createForm">
                            @csrf
                            <input type="hidden" id="resume_text_for_ai" value="" autocomplete="off">

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                <div class="md:col-span-4">
                                    <h5 class="text-lg font-semibold text-gray-900">Upload CV</h5>
                                </div>

                                <div class="md:col-span-8">
                                    <div class="form-group">
                                        <label class="control-label required">CV / Resume</label>
                                        <input class="form-control" type="file" name="resume" id="resume"
                                            accept=".pdf,.doc,.docx,.txt">
                                        <button type="button" id="parse-resume" class="mt-3 px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 inline-flex items-center">
                                            <i class="fa fa-magic mr-2"></i> Parse CV
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 border-t border-gray-200 pt-6">
                                <div class="md:col-span-4">
                                    <h5 class="text-lg font-semibold text-gray-900">@lang('modules.front.personalInformation')</h5>
                                </div>

                                <div class="md:col-span-8">
                                    <div class="form-group">
                                        <input type="hidden" value="" name="job_id" id="job_id">
                                        <input type="hidden" value="" name="location_id" id="location_id">
                                        <label class="control-label">@lang('menu.jobs') <span class="text-gray-500 text-sm">(optional)</span></label>
                                        <select name="job_job_location_id" id="job_job_location_id" onchange="getQuestions(this.value)"
                                            class="select2 form-control">
                                            <option value="">Create applicant without job</option>
                                            @foreach ($locations as $location)
                                                <option value="{{ $location->id }}">
                                                    {{ ucwords($location->job->title) . '(' . ucwords($location->location->location) . ')' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label required">@lang('app.name')</label>
                                        <input class="form-control" type="text" name="full_name"
                                            placeholder="@lang('app.name')">
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label required">@lang('app.email')</label>
                                        <input class="form-control" type="email" name="email"
                                            placeholder="@lang('app.email')">
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label required">@lang('app.phone')</label>
                                        <input class="form-control" type="tel" name="phone"
                                            placeholder="@lang('app.phone')">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Skills</label>
                                        <input class="form-control" type="text" name="skills" id="skills"
                                            placeholder="Skills from CV">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.address')</label>
                                        <textarea class="form-control" name="address" rows="4" cols="50" placeholder="@lang('app.address')"></textarea>
                                    </div>

                                    <div id="show-columns">
                                    </div>
                                </div>
                            </div>
                            <div id="show-sections">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 border-t border-gray-200 pt-6">
                                <div class="md:col-span-4 hidden" id="questionBoxTitle">
                                    <h5 class="text-lg font-semibold text-gray-900">@lang('modules.front.additionalDetails')</h5>
                                </div>

                                <div class="md:col-span-8 hidden" id="questionBox">

                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="button" id="save-form" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 inline-flex items-center"><i class="fa fa-check mr-2"></i>
                                    @lang('app.save')</button>
                            </div>

                        </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer-script')
    <script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        const fetchCountryState = "{{ route('jobs.fetchCountryState') }}";
        const csrfToken = "{{ csrf_token() }}";
        const selectCountry = "@lang('modules.front.selectCountry')";
        const selectState = "@lang('modules.front.selectState')";
        const selectCity = "@lang('modules.front.selectCity')";
        const pleaseWait = "@lang('app.aiGenerating')";
        const aiGenerateCoverLetterUrl = "{{ route('admin.job-applications.ai-generate-cover-letter') }}";
        const aiParseResumeUrl = "{{ route('admin.job-applications.ai-parse-resume') }}";
        const jobApplicationsIndexUrl = "{{ route('admin.job-applications.index') }}";

        let country = "";
        let state = "";
    </script>
    <script src="{{ asset('front/assets/js/location.js') }}"></script>
    <script>
        var datepicker = $('.dob').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            endDate: (new Date()).toDateString(),
        });

        $('.select2').select2({
            width: '100%'
        });

        $('#save-form').click(function() {
            submitApplicantForm();
        });

        var val = $('#job_job_location_id').val(); // get Current Selected Job
        if (val != '' && typeof val !== 'undefined') {
            getQuestions(val); // get Questions by question on page load
        }

        // get Questions on change Job
        function getQuestions(id) {
            if (typeof id === 'undefined' || id === '') {
                $('#job_id').val('');
                $('#location_id').val('');
                $('#questionBox').addClass('hidden').html('');
                $('#questionBoxTitle').addClass('hidden');
                $('#show-columns').html('');
                $('#show-sections').html('');
                return;
            }

            var url = "{{ route('admin.job-applications.question', ':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'GET',
                url: url,
                container: '#createForm',
                success: function(response) {
                    $('#job_id').val(response.jobJobLocation.job_id)
                    $('#location_id').val(response.jobJobLocation.location_id)
                    if (response.status == "success") {
                        if (response.count > 0) { // Question Found for selected job
                            $('#questionBox').removeClass('hidden');
                            $('#questionBoxTitle').removeClass('hidden');
                            $('#questionBox').html(response.view);
                        } else { // Question Not Found for selected job
                            $('#questionBox').addClass('hidden');
                            $('#questionBoxTitle').addClass('hidden');
                        }
                        $('#show-columns').html(response.requiredColumnsView);
                        $('#show-sections').html(response.requiredSectionsView);
                        if (response.requiredColumnsView !== '') {
                            var datepicker = $('.dob').datepicker({
                                autoclose: true,
                                format: 'yyyy-mm-dd',
                                endDate: (new Date()).toDateString(),
                            });

                            $('.select2').select2({
                                width: '100%'
                            });

                            var loc = new locationInfo()
                            loc.getCountries()
                        }
                    }
                }
            });
        }

        $('#parse-resume').click(function() {
            var $btn = $(this);
            var $form = $('#createForm');
            var input = $form.find('input[name="resume"]')[0];

            if (!input || !input.files || !input.files.length) {
                alert('Please upload a CV first.');
                return;
            }

            var prevHtml = $btn.html();
            $btn.prop('disabled', true).html(pleaseWait);

            parseResumeForAiIfSelected($form, function() {
                $btn.prop('disabled', false).html(prevHtml);
                if (typeof $.toast === 'function') {
                    $.toast({
                        text: 'CV parsed successfully.',
                        position: 'top-right',
                        loaderBg: '#00c292',
                        icon: 'success',
                        hideAfter: 3000,
                    });
                }
            }, function() {
                $btn.prop('disabled', false).html(prevHtml);
            });
        });

        function submitApplicantForm() {
            var $form = $('#createForm');
            var fd = new FormData($form[0]);

            $.ajax({
                url: '{{ route('admin.job-applications.store') }}',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    if (typeof $.easyBlockUI === 'function') {
                        $.easyBlockUI('#createForm');
                    }
                },
                complete: function() {
                    if (typeof $.easyUnblockUI === 'function') {
                        $.easyUnblockUI('#createForm');
                    }
                },
                success: function(response) {
                    if (response && response.status === 'success') {
                        window.location.href = jobApplicationsIndexUrl;
                    }
                },
                error: function(response) {
                    handleFails(response);
                }
            });
        }

        // AI generate cover letter + (optionally) fill other personal details.
        $(document).on('click', '.ai-generate-cover-letter', function() {
            var $btn = $(this);
            var jobId = $('#job_id').val();
            var locationId = $('#location_id').val();

            if (typeof jobId === 'undefined' || jobId === '') {
                alert('Please select job and location first.');
                return;
            }

            var $form = $('#createForm');
            var prevHtml = $btn.html();
            $btn.prop('disabled', true).html(pleaseWait);
            var afterResumePrepared = function() {
                var payload = {
                    _token: csrfToken,
                    job_id: jobId,
                    location_id: (locationId && locationId !== '') ? locationId : null,
                    full_name: $form.find('input[name="full_name"]').val(),
                    email: $form.find('input[name="email"]').val(),
                    phone: $form.find('input[name="phone"]').val(),
                    address: $form.find('textarea[name="address"]').val(),
                    cover_letter: $form.find('textarea[name="cover_letter"]').val(),
                    resume_text: $('#resume_text_for_ai').val() || ''
                };

                $.easyAjax({
                    url: aiGenerateCoverLetterUrl,
                    container: '#createForm',
                    type: "POST",
                    redirect: false,
                    data: payload,
                    success: function(res) {
                        try {
                            var data = res || {};
                            // Fill only if empty to avoid overwriting manually entered values.
                            var $fullName = $form.find('input[name="full_name"]');
                            if ($fullName.val().trim() === '' && data.full_name) $fullName.val(data.full_name);

                            var $email = $form.find('input[name="email"]');
                            if ($email.val().trim() === '' && data.email) $email.val(data.email);

                            var $phone = $form.find('input[name="phone"]');
                            if ($phone.val().trim() === '' && data.phone) $phone.val(data.phone);

                            var $address = $form.find('textarea[name="address"]');
                            if ($address.val().trim() === '' && data.address) $address.val(data.address);

                            var $coverLetter = $form.find('textarea[name="cover_letter"]');
                            if ($coverLetter.val().trim() === '' && data.cover_letter) {
                                $coverLetter.val(data.cover_letter);
                            } else if (data.cover_letter && data.cover_letter.trim() !== '') {
                                // If cover letter already has content, still update it when it's empty-only.
                                // (This keeps the "auto fill" behavior predictable.)
                            }
                        } catch (e) {
                            console.error(e);
                        } finally {
                            $btn.prop('disabled', false).html(prevHtml);
                        }
                    },
                    error: function(response) {
                        $btn.prop('disabled', false).html(prevHtml);
                        handleFails(response);
                    }
                });
            };

            parseResumeForAiIfSelected($form, afterResumePrepared, function() {
                $btn.prop('disabled', false).html(prevHtml);
            });
        });

        function normalizeAiSkills(data) {
            var skills = data.skills || data.skill || data.skills_text || '';
            if ($.isArray(skills)) {
                return skills.filter(Boolean).join(', ');
            }
            return skills ? String(skills) : '';
        }

        function applyResumeAiFields(data, $form) {
            if (!data || typeof data !== 'object') return;
            var $fullName = $form.find('input[name="full_name"]');
            if ($fullName.length && String($fullName.val()).trim() === '' && data.full_name) {
                $fullName.val(data.full_name);
            }
            var $email = $form.find('input[name="email"]');
            if ($email.length && String($email.val()).trim() === '' && data.email) {
                $email.val(data.email);
            }
            var $phone = $form.find('input[name="phone"]');
            if ($phone.length && String($phone.val()).trim() === '' && data.phone) {
                $phone.val(data.phone);
            }
            var $skills = $form.find('input[name="skills"]');
            var parsedSkills = normalizeAiSkills(data);
            if ($skills.length && String($skills.val()).trim() === '' && parsedSkills) {
                $skills.val(parsedSkills);
            }
            var $address = $form.find('textarea[name="address"]');
            if ($address.length && String($address.val()).trim() === '' && data.address) {
                $address.val(data.address);
            }
            var $city = $form.find('input[name="city"]');
            if ($city.length && String($city.val()).trim() === '' && data.city) {
                $city.val(data.city);
            }
            var $zipCode = $form.find('input[name="zip_code"]');
            if ($zipCode.length && String($zipCode.val()).trim() === '' && data.zip_code) {
                $zipCode.val(data.zip_code);
            }
            var $coverLetter = $form.find('textarea[name="cover_letter"]');
            if ($coverLetter.length && String($coverLetter.val()).trim() === '' && data.cover_letter) {
                $coverLetter.val(data.cover_letter);
            }
            if ($('#countryId').length && data.country) {
                country = data.country;
                state = data.state || '';
                var loc = new locationInfo();
                loc.getCountries();
            }
            if (data.resume_text) {
                $('#resume_text_for_ai').val(data.resume_text);
            }
        }

        function parseResumeForAiIfSelected($form, onSuccess, onError) {
            var input = $form.find('input[name="resume"]')[0];
            if (!input || !input.files || !input.files.length) {
                onSuccess();
                return;
            }

            var fd = new FormData();
            fd.append('_token', csrfToken);
            fd.append('resume', input.files[0]);

            $.ajax({
                url: aiParseResumeUrl,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    if (typeof $.easyBlockUI === 'function') {
                        $.easyBlockUI('#createForm');
                    }
                },
                complete: function() {
                    if (typeof $.easyUnblockUI === 'function') {
                        $.easyUnblockUI('#createForm');
                    }
                },
                success: function(response) {
                    if (response && response.status === 'success') {
                        applyResumeAiFields(response, $('#createForm'));
                        onSuccess();
                        return;
                    }

                    var failMsg = (response && response.message) ? response.message : 'Resume parsing failed.';
                    if (typeof $.toast === 'function') {
                        $.toast({
                            text: failMsg,
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'error',
                            hideAfter: 5000,
                        });
                    }
                    if (typeof onError === 'function') onError();
                },
                error: function(xhr) {
                    var msg = 'Resume parsing failed.';
                    try {
                        var j = xhr.responseJSON;
                        if (j && j.message) msg = j.message;
                    } catch (e) {}
                    if (typeof $.toast === 'function') {
                        $.toast({
                            text: msg,
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'error',
                            hideAfter: 5000,
                        });
                    }
                    if (typeof onError === 'function') onError();
                }
            });
        }

        // Do not auto-run AI on resume upload; only clear stale extracted text.
        $(document).on('change', '#createForm input[name="resume"]', function() {
            $('#resume_text_for_ai').val('');
        });

        function handleFails(response) {

            if (typeof response.responseJSON.errors != "undefined") {
                var keys = Object.keys(response.responseJSON.errors);
                $('#createForm').find(".has-error").find(".help-block").remove();
                $('#createForm').find(".has-error").removeClass("has-error");

                for (var i = 0; i < keys.length; i++) {
                    // Escape dot that comes with error in array fields
                    var key = keys[i].replace(".", '\\.');
                    var formarray = keys[i];

                    // If the response has form array
                    if (formarray.indexOf('.') > 0) {
                        var array = formarray.split('.');
                        response.responseJSON.errors[keys[i]] = response.responseJSON.errors[keys[i]];
                        key = array[0] + '[' + array[1] + ']';
                    }

                    var ele = $('#createForm').find("[name='" + key + "']");

                    var grp = ele.closest(".form-group");
                    $(grp).find(".help-block").remove();

                    //check if wysihtml5 editor exist
                    var wys = $(grp).find(".wysihtml5-toolbar").length;

                    if (wys > 0) {
                        var helpBlockContainer = $(grp);
                    } else {
                        var helpBlockContainer = $(grp).find("div:first");
                    }
                    if ($(ele).is(':radio')) {
                        helpBlockContainer = $(grp);
                    }

                    if (helpBlockContainer.length == 0) {
                        helpBlockContainer = $(grp);
                    }

                    helpBlockContainer.append('<div class="help-block">' + response.responseJSON.errors[keys[i]] +
                        '</div>');
                    $(grp).addClass("has-error");
                }

                if (keys.length > 0) {
                    var element = $("[name='" + keys[0] + "']");
                    if (element.length > 0) {
                        $("html, body").animate({
                            scrollTop: element.offset().top - 150
                        }, 200);
                    }
                }
            }
        }
    </script>
@endpush
