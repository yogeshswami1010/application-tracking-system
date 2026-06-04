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
                            <div class="form-group mb-4">
                            <label class="control-label font-semibold">Entry Type</label>
                                <div class="flex gap-4 mt-2">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="entry_type" value="applicant" checked
                                            class="text-blue-600" id="entry-type-applicant">
                                        <span class="text-sm font-medium text-gray-700">Job Applicant</span>
                                        <span class="text-xs text-gray-400">(shown on board)</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="entry_type" value="candidate"
                                            class="text-blue-600" id="entry-type-candidate">
                                        <span class="text-sm font-medium text-gray-700">Candidate Database</span>
                                        <span class="text-xs text-gray-400">(not a job applicant)</span>
                                    </label>
                                </div>
                        </div>

                        {{-- Job field wrapper — hide when candidate --}}
                        <div id="job-field-wrapper">
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
                                    <div class="form-group mt-4">
                                        <label class="control-label">@lang('modules.jobApplication.applicantNotes')</label>
                                        <div id="create-notes-list" class="space-y-2 mb-3"></div>
                                        <div class="flex gap-2">
                                            <textarea name="notes_input" id="notes_input" rows="2"
                                                class="form-control flex-1 resize-none"
                                                placeholder="@lang('modules.jobApplication.addNote')"></textarea>
                                            <button type="button" id="add-create-note"
                                                class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium self-end whitespace-nowrap">
                                                <i class="fa fa-plus mr-1"></i> Add
                                            </button>
                                        </div>
                                        <div id="create-notes-hidden"></div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mammoth@1.8.0/mammoth.browser.min.js"></script>
    <script>
        const fetchCountryState = "{{ route('jobs.fetchCountryState') }}";
        const csrfToken = "{{ csrf_token() }}";
        const selectCountry = "@lang('modules.front.selectCountry')";
        const selectState = "@lang('modules.front.selectState')";
        const selectCity = "@lang('modules.front.selectCity')";
        const pleaseWait = "@lang('app.aiGenerating')";
        const resumeParsingText = "Parsing CV...";
        const aiGenerateCoverLetterUrl = "{{ route('admin.job-applications.ai-generate-cover-letter') }}";
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
        // Entry type toggle
        $('input[name="entry_type"]').on('change', function () {
            if ($(this).val() === 'candidate') {
                $('#job-field-wrapper').hide();
                $('#job_job_location_id').val('').trigger('change');
                // clear job/location hidden fields
                $('#job_id').val('');
                $('#location_id').val('');
                $('#questionBox').addClass('hidden').html('');
                $('#questionBoxTitle').addClass('hidden');
                $('#show-columns').html('');
                $('#show-sections').html('');
            } else {
                $('#job-field-wrapper').show();
            }
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
            $btn.prop('disabled', true).html(resumeParsingText);

            parseResumeIfSelected($form, function() {
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
            var selectedJobLocation = $('#job_job_location_id').val();

            if (!selectedJobLocation) {
                fd.delete('job_job_location_id');
                fd.delete('job_id');
                fd.delete('location_id');
            }

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

            parseResumeIfSelected($form, afterResumePrepared, function() {
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

        function applyParsedResumeFields(data, $form) {
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

        function readFileAsText(file) {
            return new Promise(function(resolve, reject) {
                var reader = new FileReader();
                reader.onload = function(e) { resolve(e.target.result || ''); };
                reader.onerror = function() { reject('Unable to read this CV file.'); };
                reader.readAsText(file);
            });
        }

        function readFileAsArrayBuffer(file) {
            return new Promise(function(resolve, reject) {
                var reader = new FileReader();
                reader.onload = function(e) { resolve(e.target.result); };
                reader.onerror = function() { reject('Unable to read this CV file.'); };
                reader.readAsArrayBuffer(file);
            });
        }

        function extractPdfText(file) {
            if (typeof pdfjsLib === 'undefined') {
                return Promise.reject('PDF parser is not loaded. Please check the pdf.js script.');
            }
            if (pdfjsLib.GlobalWorkerOptions) {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            }

            return readFileAsArrayBuffer(file).then(function(buffer) {
                return pdfjsLib.getDocument({ data: buffer }).promise;
            }).then(function(pdf) {
                var pages = [];
                for (var i = 1; i <= pdf.numPages; i++) {
                    pages.push(pdf.getPage(i).then(function(page) {
                        return page.getTextContent();
                    }).then(function(content) {
                        return content.items.map(function(item) {
                            return item.str;
                        }).join(' ');
                    }));
                }
                return Promise.all(pages).then(function(textPages) {
                    return textPages.join('\n');
                });
            });
        }

        function extractDocxText(file) {
            if (typeof mammoth === 'undefined') {
                return Promise.reject('DOCX parser is not loaded. Please check the mammoth script.');
            }

            return readFileAsArrayBuffer(file).then(function(buffer) {
                return mammoth.extractRawText({ arrayBuffer: buffer });
            }).then(function(result) {
                return result.value || '';
            });
        }

        function extractResumeText(file) {
            var name = (file.name || '').toLowerCase();
            var type = (file.type || '').toLowerCase();

            if (type.indexOf('pdf') >= 0 || name.endsWith('.pdf')) {
                return extractPdfText(file);
            }
            if (name.endsWith('.docx')) {
                return extractDocxText(file);
            }
            if (type.indexOf('text') >= 0 || name.endsWith('.txt')) {
                return readFileAsText(file);
            }

            return Promise.reject('Please upload a PDF, DOCX, or TXT CV for browser parsing.');
        }

        function escapeRegExp(value) {
            return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function pickResumeName(text) {
            var badHeadings = /^(resume|curriculum vitae|cv|profile|summary|objective|contact|email|phone|mobile|address|skills|technical skills|education|experience|work experience|projects|certifications)$/i;
            var lines = String(text || '').split(/\n+/).map(function(line) {
                return line.replace(/\s+/g, ' ').trim();
            }).filter(Boolean).slice(0, 25);

            for (var i = 0; i < lines.length; i++) {
                var line = lines[i];
                if (badHeadings.test(line) || line.indexOf('@') >= 0 || /\d/.test(line) || line.length > 80) {
                    continue;
                }
                if (/^[a-zA-Z][a-zA-Z .'-]{1,79}$/.test(line) && line.split(/\s+/).length <= 5) {
                    return line;
                }
            }

            return '';
        }

        function parseResumeSkills(text) {
            var knownSkills = [
                'PHP', 'Laravel', 'JavaScript', 'TypeScript', 'Vue', 'React', 'Angular', 'Node.js', 'Express',
                'HTML', 'CSS', 'Tailwind', 'Bootstrap', 'jQuery', 'MySQL', 'PostgreSQL', 'MongoDB', 'Redis',
                'Git', 'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'Python', 'Django', 'Flask', 'Java',
                'Spring', 'C#', '.NET', 'SQL', 'REST API', 'GraphQL', 'Excel', 'Power BI', 'Tableau',
                'Communication', 'Leadership', 'Project Management', 'Sales', 'Marketing', 'Recruitment'
            ];
            var found = [];
            var fullText = String(text || '');

            knownSkills.forEach(function(skill) {
                var pattern = new RegExp('(^|[^a-zA-Z0-9+#.])' + escapeRegExp(skill) + '([^a-zA-Z0-9+#.]|$)', 'i');
                if (pattern.test(fullText)) {
                    found.push(skill);
                }
            });

            var section = fullText.match(/(?:^|\n)\s*(?:technical skills|key skills|skills)\s*[:\n]+([\s\S]{0,900}?)(?=\n\s*(?:experience|work experience|employment|education|projects|certifications|summary|profile|objective|languages)\b|$)/i);
            if (section && section[1]) {
                section[1].split(/[,|;\n-]+/).forEach(function(item) {
                    var skill = item.replace(/\s+/g, ' ').trim();
                    if (skill && skill.length <= 40) {
                        found.push(skill);
                    }
                });
            }

            var unique = [];
            found.forEach(function(skill) {
                if (unique.map(function(s) { return s.toLowerCase(); }).indexOf(skill.toLowerCase()) === -1) {
                    unique.push(skill);
                }
            });

            return unique.slice(0, 30).join(', ');
        }

        function parseResumeText(text) {
            var cleanText = String(text || '').replace(/\r/g, '\n').replace(/\t/g, ' ');
            var emailMatch = cleanText.match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i);
            var phoneMatch = cleanText.match(/(?:\+?\d[\d\s().-]{7,}\d)/);

            return {
                full_name: pickResumeName(cleanText),
                email: emailMatch ? emailMatch[0] : '',
                phone: phoneMatch ? phoneMatch[0].replace(/\s+/g, ' ').trim() : '',
                skills: parseResumeSkills(cleanText),
                resume_text: cleanText
            };
        }

        function parseResumeIfSelected($form, onSuccess, onError) {
            var input = $form.find('input[name="resume"]')[0];
            if (!input || !input.files || !input.files.length) {
                onSuccess();
                return;
            }

            if (typeof $.easyBlockUI === 'function') {
                $.easyBlockUI('#createForm');
            }

            extractResumeText(input.files[0]).then(function(text) {
                if (!String(text || '').trim()) {
                    throw 'No readable text found in this CV.';
                }

                applyParsedResumeFields(parseResumeText(text), $('#createForm'));
                onSuccess();
            }).catch(function(error) {
                var msg = error || 'CV parsing failed.';
                if (typeof $.toast === 'function') {
                    $.toast({
                        text: msg,
                        position: 'top-right',
                        loaderBg: '#ff6849',
                        icon: 'error',
                        hideAfter: 5000,
                    });
                } else {
                    alert(msg);
                }
                if (typeof onError === 'function') onError();
            }).finally(function() {
                if (typeof $.easyUnblockUI === 'function') {
                    $.easyUnblockUI('#createForm');
                }
            });
        }

        // Do not auto-run parsing on resume upload; only clear stale extracted text.
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
        // Notes on create
var createNotes = [];

$('#add-create-note').on('click', function () {
    var text = $('#notes_input').val().trim();
    if (!text) return;

    var index = createNotes.length;
    createNotes.push(text);

    // Render note pill
    $('#create-notes-list').append(
        '<div class="flex items-start justify-between bg-gray-50 rounded-lg p-3 border-l-4 border-blue-400" id="cn-' + index + '">' +
            '<p class="text-sm text-gray-700 flex-1">' + $('<div>').text(text).html() + '</p>' +
            '<button type="button" class="remove-create-note ml-3 text-red-400 hover:text-red-600 text-xs" data-index="' + index + '">' +
                '<i class="fa fa-times"></i>' +
            '</button>' +
        '</div>'
    );

    // Add hidden input so it submits with the form
    $('#create-notes-hidden').append(
        '<input type="hidden" name="notes[]" id="cn-hidden-' + index + '" value="' + $('<div>').text(text).html() + '">'
    );

    $('#notes_input').val('');
});

$(document).on('click', '.remove-create-note', function () {
    var index = $(this).data('index');
    createNotes[index] = null;
    $('#cn-' + index).remove();
    $('#cn-hidden-' + index).remove();
});
    </script>
@endpush
