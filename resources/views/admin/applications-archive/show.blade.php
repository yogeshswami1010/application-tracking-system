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
                <h2 class="text-2xl font-extrabold text-white leading-tight">
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

        <div class="p-4 space-y-4">
        
            <div class="rounded-2xl bg-white shadow-sm border border-gray-100 p-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">
                    Contact Information
                </h3>

                <div class="space-y-4">

                    <div>
                        <p class="text-xs text-gray-400">Email</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $application->email }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400">Phone</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $application->phone }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400">Applied At</p>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $application->created_at->format('d M Y h:i A') }}
                        </p>
                    </div>

                </div>
            </div>
            @if(
                    $application->gender ||
                    $application->dob ||
                    $application->country
                )

                <div class="rounded-2xl bg-white shadow-sm border border-gray-100 p-4">

                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">
                        Personal Information
                    </h3>

                    <div class="grid grid-cols-2 gap-4">

                        @if($application->gender)
                        <div>
                            <p class="text-xs text-gray-400">Gender</p>
                            <p class="font-medium">
                                {{ ucfirst($application->gender) }}
                            </p>
                        </div>
                        @endif

                        @if($application->dob)
                        <div>
                            <p class="text-xs text-gray-400">Date Of Birth</p>
                            <p class="font-medium">
                                {{ $application->dob->format('d M Y') }}
                            </p>
                        </div>
                        @endif

                        @if($application->country)
                        <div>
                            <p class="text-xs text-gray-400">Country</p>
                            <p class="font-medium">
                                {{ $application->country }}
                            </p>
                        </div>
                        @endif

                        @if($application->state)
                        <div>
                            <p class="text-xs text-gray-400">State</p>
                            <p class="font-medium">
                                {{ $application->state }}
                            </p>
                        </div>
                        @endif

                        @if($application->city)
                        <div>
                            <p class="text-xs text-gray-400">City</p>
                            <p class="font-medium">
                                {{ $application->city }}
                            </p>
                        </div>
                        @endif

                    </div>

                </div>

                @endif

                @if($answers->count())

                <div class="rounded-2xl bg-white shadow-sm border border-gray-100 p-4">

                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">
                        Additional Details
                    </h3>

                    <div class="space-y-4">

                        @foreach($answers as $answer)

                        <div class="rounded-xl bg-gray-50 p-3">

                            <p class="text-sm font-semibold text-gray-900">
                                {{ $answer->question->question }}
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ ucfirst($answer->answer) }}
                            </p>

                        </div>

                        @endforeach

                    </div>

                </div>

                @endif
              <p>Resume URL: {{ $application->resume_url }}</p>

<p>Documents Count:
    {{ $application->documents ? $application->documents->count() : 0 }}
</p>

@if($application->documents)
    @foreach($application->documents as $doc)
        <p>
            {{ $doc->name }} -
            {{ $doc->hashname }}
        </p>
    @endforeach
@endif

                <div class="rounded-2xl bg-white shadow-sm border border-gray-100 p-4">

      
                    <div class="flex flex-wrap gap-2" id="resume-{{ $application->id }}">
                        @if ($application->resume_url)
                          <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">
                                Resume
                            </h3>
                            <a target="_blank" href="{{ $application->resume_url }}"
                            class="inline-flex flex-1 min-w-[8rem] items-center justify-center gap-2 rounded-[10px] bg-[#2563EB] px-4 py-2.5 text-center text-[12.5px] font-bold text-white transition hover:bg-[#1d4ed8]">
                                <i class="fa fa-file-pdf-o"></i>
                                @lang('app.view') @lang('modules.jobApplication.resume')
                            </a>
                        @endif
                        @if($user->cans('add_schedule') && $application->status->status == 'interview' && is_null($application->schedule))
                            <a onclick="createSchedule('{{$application->id}}')" href="javascript:;"
                            class="inline-flex flex-[2] min-w-[10rem] items-center justify-center gap-2 rounded-[10px] bg-[#2563EB] px-4 py-2.5 text-center text-[12.5px] font-bold text-white transition hover:bg-[#1d4ed8]">
                                <i class="fa fa-calendar-plus-o"></i>
                                @lang('modules.interviewSchedule.scheduleInterview')
                            </a>
                        @endif
                        @if($application->status->status == 'hired' && is_null($application->onboard))
                            <a href="{{ route('admin.job-onboard.create') }}?id={{$application->id}}"
                            class="inline-flex flex-1 min-w-[8rem] items-center justify-center gap-2 rounded-[10px] bg-[#059669] px-4 py-2.5 text-center text-[12.5px] font-bold text-white transition hover:bg-[#047857]">
                                <i class="fa fa-rocket"></i>
                                @lang('app.startOnboard')
                            </a>
                        @endif
                    </div>

                </div>

          
           <div class="rounded-2xl bg-white shadow-sm border border-gray-100 p-4">

                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">
                    Skills
                </h3>

                <select
                    name="skills[]"
                    id="skills"
                    class="form-control select2 custom-select"
                    multiple>

                    @foreach($skills as $skill)
                        <option
                            @if(!is_null($application->skills) && in_array($skill->id, $application->skills))
                                selected
                            @endif
                            value="{{ $skill->id }}">
                            {{ $skill->name }}
                        </option>
                    @endforeach

                </select>

                <div class="mt-3">

                    <a href="javascript:addSkills({{ $application->id }});"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">

                        <i class="fa fa-save mr-2"></i>
                        Save Skills

                    </a>

                </div>

            </div>
                <div class="rounded-2xl bg-white shadow-sm border border-gray-100 p-4">

                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">
                    Applicant Notes
                </h3>

                <div id="applicant-notes">

                    @forelse($application->notes as $notes)

                        <div class="border-l-4 border-blue-500 bg-blue-50 rounded-r-xl p-3 mb-3">

                            <div class="flex justify-between items-center">

                                <span class="font-semibold text-sm text-gray-900">
                                    {{ $notes->user->name }}
                                </span>

                                <span class="text-xs text-gray-500">
                                    {{ $notes->created_at->diffForHumans() }}
                                </span>

                            </div>

                            <p class="mt-2 text-sm text-gray-700">
                                {{ ucfirst($notes->note_text) }}
                            </p>

                        </div>

                    @empty

                        <div class="text-center py-6 text-gray-400">
                            No notes added yet.
                        </div>

                    @endforelse

                </div>

                @if($user->cans('edit_job_applications'))

                    <div class="mt-4 border-t pt-4">

                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Add Note
                        </label>

                        <textarea
                            id="note_text"
                            rows="3"
                            class="form-control rounded-xl"
                            placeholder="Write a note about this applicant..."></textarea>

                        <button
                            type="button"
                            id="add-note"
                            class="mt-3 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">

                            <i class="fa fa-plus"></i>
                            Add Note

                        </button>

                    </div>

                @endif

            </div>
            </div>
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