@php
    $detailCatName = optional($application->job->category)->name ?? __('app.category');
    $detailCatKey = \Illuminate\Support\Str::slug($detailCatName);

    $detailCatClass = match (true) {
        str_contains($detailCatKey, 'engineer') ||
        str_contains($detailCatKey, 'tech') ||
        str_contains($detailCatKey, 'it')
            => 'bg-[#EFF6FF] text-[#1D4ED8]',

        str_contains($detailCatKey, 'sale') ||
        str_contains($detailCatKey, 'market')
            => 'bg-[#FFF7ED] text-[#C2410C]',

        str_contains($detailCatKey, 'content') ||
        str_contains($detailCatKey, 'design')
            => 'bg-[#ECFDF5] text-[#065F46]',

        str_contains($detailCatKey, 'hr') ||
        str_contains($detailCatKey, 'people')
            => 'bg-[#F5F3FF] text-[#5B21B6]',

        default => 'bg-[#F1F3F7] text-[#5A6478]',
    };
@endphp
<link rel="stylesheet" href="{{ asset('assets/plugins/jquery-bar-rating-master/dist/themes/fontawesome-stars.css') }}">
<style>

    .right-panel-box {
        overflow-y: auto;
        overflow-x: hidden;
        max-height: 70vh;
        padding-right: 10px;
    }

    .resume-button {
        text-align: center;
        /* margin-top: 1rem */
        margin-right: 38px;
    }

    .star-center{
        margin-right: 42px;
    }



</style>
<div class="rpanel-title"> @lang('menu.jobApplications') <span><i class="ti-close right-side-toggle"></i></span></div>
<div class="ja-applicant-detail max-h-[calc(100vh-0px)] overflow-y-auto bg-[#F8F7F4]">

    <div class="row">
        <div class="relative bg-gradient-to-br from-[#0F1F3D] to-[#162849] px-5 pb-6 pt-6">

        <button type="button"
            class="right-side-toggle absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-white/10 text-white hover:bg-white/20">
            <i class="fa fa-times text-sm"></i>
        </button>

        <div class="flex items-start gap-4">
            <img src="{{ $application->photo_url }}"
                alt="{{ $application->full_name }}"
                class="h-16 w-16 rounded-full border-2 border-white object-cover shadow-lg">

            <div class="flex-1">
                <h2 class="text-xl font-bold text-white">
                    {{ ucwords($application->full_name) }}
                </h2>

                <p class="mt-1 text-sm text-white/70">
                    {{ ucwords($application->job->title) }}
                </p>

                @if(optional($application->location)->location)
                    <p class="mt-1 text-xs text-white/50">
                        <i class="fa fa-map-marker mr-1"></i>
                        {{ optional($application->location)->location }}
                    </p>
                @endif

                <div class="mt-3 flex flex-wrap gap-2">

                    <span class="rounded-full bg-red-500 px-3 py-1 text-xs font-semibold text-white">
                        Archived
                    </span>

                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $detailCatClass }}">
                        {{ ucfirst($detailCatName) }}
                    </span>

                    @if($application->rating)
                        <span class="rounded-full bg-yellow-100 text-yellow-700 px-3 py-1 text-xs font-semibold">
                            ⭐ {{ $application->rating }}/5
                        </span>
                    @endif

                </div>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">

            @if ($application->resume_url)
                <a target="_blank"
                    href="{{ $application->resume_url }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <i class="fa fa-file-pdf-o"></i>
                    @lang('app.view') @lang('modules.jobApplication.resume')
                </a>
            @endif

            @if ($user->cans('delete_job_applications'))
                <a href="javascript:unarchiveApplication({{ $application->id }})"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                    <i class="fa fa-refresh"></i>
                    @lang('modules.jobApplication.unarchiveApplication')
                </a>
            @endif

        </div>
    </div>

        <div class="col-md-8 right-panel-box">
        

            <div class="w-full">
                <strong>@lang('app.email')</strong><br>
                <p class="text-muted">{{ $application->email }}</p>
            </div>

            <div class="w-full">
                <strong>@lang('app.phone')</strong><br>
                <p class="text-muted">{{ $application->phone }}</p>
            </div>

            <div class="w-full">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if (!is_null($application->gender))
                        <div class="w-full md:col-span-1">
                            <strong>@lang('app.gender')</strong><br>
                            <p class="text-muted" id="gender-{{ $application->id }}">{{ ucfirst($application->gender) }}</p>
                        </div>
                    @endif
                    @if (!is_null($application->dob))
                        <div class="w-full md:col-span-1">
                            <strong>@lang('app.dob')</strong><br>
                            <p class="text-muted" id="dob-{{ $application->id }}">{{ $application->dob->format('jS F, Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if (!is_null($application->country))
                <div class="w-full">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1">
                            <strong>@lang('app.country')</strong><br>
                            <p class="text-muted" id="country-{{ $application->id }}">{{ $application->country }}</p>
                        </div>
                        <div class="col">
                            <strong>@lang('app.state')</strong><br>
                            <p class="text-muted" id="state-{{ $application->id }}">{{ $application->state }}</p>
                        </div>
                        <div class="col">
                            <strong>@lang('app.city')</strong><br>
                            <p class="text-muted" id="city-{{ $application->id }}">{{ $application->city }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="w-full">
                <strong>@lang('modules.jobApplication.appliedAt')</strong><br>
                <p class="text-muted">{{ $application->created_at->format('d M, Y H:i') }}</p>
            </div>
            @if ($answers->count() > 0)
                <div class="w-full">
                    <h4>@lang('modules.front.additionalDetails')</h4>
                    @forelse($answers as $answer)
                        <strong>{{$answer->question->question}}</strong><br>
                        <p class="text-muted">{{ ucfirst($answer->answer)}}</p>
                    @empty
                    @endforelse
                </div>
            @endif
            @if(!is_null($application->schedule))
                <hr>

                <h5>@lang('modules.interviewSchedule.scheduleDetail')</h5>
                <div class="w-full">
                    <strong>@lang('modules.interviewSchedule.scheduleDate')</strong><br>
                    <p class="text-muted">{{ $application->schedule->schedule_date->format('d M, Y H:i') }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="w-full">
                        <strong>@lang('modules.interviewSchedule.assignedEmployee')</strong><br>
                    </div>
                    <div class="col-sm-6">
                        <strong>@lang('modules.interviewSchedule.employeeResponse')</strong><br>
                    </div>
                    @forelse($application->schedule->employee as $key => $emp )
                        <div class="w-full">
                            <p class="text-muted">{{ ucwords($emp->user->name) }}</p>
                        </div>

                        <div class="col-sm-6">
                            @if($emp->user_accept_status == 'accept')
                                <label class="badge badge-success">{{ ucwords($emp->user_accept_status) }}</label>
                            @elseif($emp->user_accept_status == 'refuse')
                                <label class="badge badge-danger">{{ ucwords($emp->user_accept_status) }}</label>
                            @else
                                <label class="badge badge-warning">{{ ucwords($emp->user_accept_status) }}</label>
                            @endif
                        </div>
                    @empty
                    @endforelse
                </div>

            @endif

            @if(isset($application->schedule->comments) == 'interview' && count($application->schedule->comments) > 0)
                <hr>

                <h5>@lang('modules.interviewSchedule.comments')</h5>
                @forelse($application->schedule->comments as $key => $comment )

                    <div class="w-full">
                        <p class="text-muted"><b>{{$comment->user->name }}:</b> {{ $comment->comment }}</p>
                    </div>
                @empty
                @endforelse

            @endif
            <div class="w-full">
                <p class="text-muted">
                    @if(!is_null($application->skype_id))
                        <span class="skype-button rounded" data-contact-id="live:{{$application->skype_id}}"
                              data-text="Call"></span>
                    @endif
                </p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                @if($user->cans('add_schedule') && $application->status->status == 'interview' && is_null($application->schedule))
                    <div class="w-full">
                        <p class="text-muted">
                            <a onclick="createSchedule('{{$application->id}}')" href="javascript:;"
                               class="btn btn-sm btn-info">@lang('modules.interviewSchedule.scheduleInterview')</a>
                        </p>
                    </div>
                @endif
            </div>
        </div>
        @if ($user->cans('edit_job_applications'))
            <div class="w-full" id="skills-container">
                <hr>
                <div class="w-full mb-3">
                    <h5>@lang('modules.jobApplication.skills')</h5>
                </div>
                <div class="form-group mb-2">
                    <select name="skills[]" id="skills" class="form-control select2 custom-select" multiple>
                        @forelse ($skills as $skill)
                            <option @if (!is_null($application->skills) && in_array($skill->id, $application->skills)) selected @endif value="{{ $skill->id }}">{{ $skill->name }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
                <a href="javascript:addSkills({{ $application->id}});" id="add-skills" class="btn btn-sm btn-outline-success">
                    @if (!is_null($application->skills) && sizeof($application->skills) > 0)
                        @lang('modules.jobApplication.updateSkills')
                    @else
                        @lang('modules.jobApplication.addSkills')
                    @endif
                </a>
            </div>
        @endif
        <div class="w-full">
            <hr>
            <div class="w-full mb-3">
                <h5>@lang('modules.jobApplication.applicantNotes')</h5>
            </div>

            <div id="applicant-notes" class="w-full">
                <ul class="list-none space-y-3">
                    @foreach($application->notes as $key => $notes )
                        <li class="flex items-start mb-3" id="note-{{ $notes->id }}">
                            <div class="flex-1">
                                <h6 class="mt-0 mb-1 flex items-center justify-between">
                                    <span>{{ ucwords($notes->user->name) }}</span>
                                    <span class="text-sm italic font-light flex items-center gap-2"><small> {{ $notes->created_at->diffForHumans() }} </small>
                                        @if($user->cans('edit_job_applications'))
                                            <a href="javascript:;" class="edit-note" data-note-id="{{ $notes->id }}"><i class="fa fa-edit ml-2"></i></a>
                                            <a href="javascript:;" class="delete-note" data-note-id="{{ $notes->id }}"><i class="fa fa-trash ml-1 text-danger"></i></a>
                                        @endif
                                </span>
                                </h6>
                                <small class="note-text">{{ ucfirst($notes->note_text) }}</small>
                                <div class="note-textarea"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if($user->cans('edit_job_applications'))
                <div class="w-full">
                    <div class="form-group mb-2">
                        <textarea name="note" id="note_text" cols="30" rows="2" class="form-control"></textarea>
                    </div>
                    <a href="javascript:;" id="add-note" class="btn btn-sm btn-outline-primary">@lang('modules.jobApplication.addNote')</a>
                </div>
            @endif

        </div>


    </div>

</div>
@if($user->cans('edit_job_applications'))
    <script src="{{ asset('assets/plugins/jquery-bar-rating-master/dist/jquery.barrating.min.js') }}"
            type="text/javascript"></script>
    <script>
        $('#example-fontawesome').barrating({
            theme: 'fontawesome-stars',
            showSelectedRating: false,
            onSelect: function (value, text, event) {
                if (event !== undefined && value !== '') {
                    var url = "{{ route('admin.job-applications.rating-save',':id') }}";
                    url = url.replace(':id', {{$application->id}});
                    var token = '{{ csrf_token() }}';
                    var id = {{$application->id}};
                    $.easyAjax({
                        type: 'Post',
                        url: url,
                        container: '#example-fontawesome',
                        data: {'rating': value, '_token': token},
                        success: function (response) {
                            $('#example-fontawesome_' + id).barrating('set', value);
                        }
                    });
                }

            }
        });
        @if($application->rating !== null)
        $('#example-fontawesome').barrating('set', {{$application->rating}});
        @endif

    </script>
@endif
<script>

    $('.select2#skills').select2();

    function addSkills(applicationId) {
        let url = "{{ route('admin.job-applications.addSkills', ':id') }}";
        url = url.replace(':id', applicationId);

        $.easyAjax({
            url: url,
            type: 'POST',
            container: '#skills-container',
            data: {
                _token: '{{ csrf_token() }}',
                skills: $('#skills').val()
            },
            success: function (response) {
                if (response.status === 'success') {
                    if (window.raCloseRightSidebar) window.raCloseRightSidebar();
                    if (typeof table !== 'undefined') {
                        table.draw(false);
                    }
                    else {
                        loadData();
                    }
                }
            }
        })
    }

    function unarchiveApplication(applicationId) {
        swal({
            title: "@lang('errors.areYouSure')",
            text: "@lang('errors.unarchiveWarning')",
            type: "info",
            showCancelButton: true,
            confirmButtonColor: "#28A745",
            confirmButtonText: "@lang('app.yes')",
            cancelButtonText: "@lang('app.no')",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm){
            if (isConfirm) {

                var url = "{{ route('admin.job-applications.unarchiveJobApplication', ':id') }}";
                url = url.replace(':id', applicationId);

                var token = '{{ csrf_token() }}';

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token':token},
                    success: function (response) {
                        if (window.raCloseRightSidebar) window.raCloseRightSidebar();
                        if (response.status === 'success') {
                            if (typeof table !== 'undefined') {
                                table.draw(false);
                            }
                            else {
                                loadData();
                            }
                        }
                    }
                });
            }
        });
    }

    $('#add-note').click(function () {
        var url = "{{ route('admin.applicant-note.store') }}";
        var id = {{$application->id}};
        var note = $('#note_text').val();
        var token = '{{ csrf_token() }}';

        $.easyAjax({
            type: 'POST',
            url: url,
            data: {'_token':token, 'id':id, 'note': note},
            success: function (response) {
                if(response.status == 'success') {
                    $('#applicant-notes').html(response.view);
                    $('#note_text').val('');
                }
            }
        });
    });

    $('body').on('click', '.edit-note', function() {
        $(this).hide();
        let noteId = $(this).data('note-id');
        $('body').find('#note-'+noteId+' .note-text').hide();

        let noteText = $('body').find('#note-'+noteId+' .note-text').html();
        let textArea = '<textarea id="edit-note-text-'+noteId+'" class="form-control" row="4">'+noteText+'</textarea><a class="update-note" data-note-id="'+noteId+'" href="javascript:;"><i class="fa fa-check"></i> @lang("app.save")</a>';
        $('body').find('#note-'+noteId+' .note-textarea').html(textArea);
    });

    $('body').on('click', '.update-note', function () {
        let noteId = $(this).data('note-id');

        var url = "{{ route('admin.applicant-note.update', ':id') }}";
        url = url.replace(':id', noteId);

        var note = $('#edit-note-text-'+noteId).val();
        var token = '{{ csrf_token() }}';

        $.easyAjax({
            type: 'POST',
            url: url,
            data: {'_token':token, 'noteId':noteId, 'note': note, '_method': 'PUT'},
            success: function (response) {
                if(response.status == 'success') {
                    $('#applicant-notes').html(response.view);
                }
            }
        });
    });

    $('body').on('click', '.delete-note', function(){
        let noteId = $(this).data('note-id');
        swal({
            title: "@lang('errors.areYouSure')",
            text: "@lang('errors.deleteWarning')",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "@lang('app.delete')",
            cancelButtonText: "@lang('app.cancel')",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm){
            if (isConfirm) {

                var url = "{{ route('admin.applicant-note.destroy', ':id') }}";
                url = url.replace(':id', noteId);

                var token = '{{ csrf_token() }}';

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token':token, '_method': 'DELETE'},
                    success: function (response) {
                        if(response.status == 'success') {
                            $('#applicant-notes').html(response.view);
                        }
                    }
                });
            }
        });
    });
</script>
@if(!is_null($application->skype_id))
    <script src="https://swc.cdn.skype.com/sdk/v1/sdk.min.js"></script>
@endif