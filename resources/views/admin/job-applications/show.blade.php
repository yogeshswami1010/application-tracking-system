<link rel="stylesheet" href="{{ asset('assets/plugins/jquery-bar-rating-master/dist/themes/fontawesome-stars.css') }}">
@php
    $detailCatName = $application->job?->category?->name ?? __('app.category');
    $detailCatKey = \Illuminate\Support\Str::slug($detailCatName);
    $detailCatClass = match (true) {
        str_contains($detailCatKey, 'engineer') || str_contains($detailCatKey, 'tech') || str_contains($detailCatKey, 'it') => 'bg-[#EFF6FF] text-[#1D4ED8]',
        str_contains($detailCatKey, 'sale') || str_contains($detailCatKey, 'market') => 'bg-[#FFF7ED] text-[#C2410C]',
        str_contains($detailCatKey, 'content') || str_contains($detailCatKey, 'design') => 'bg-[#ECFDF5] text-[#065F46]',
        str_contains($detailCatKey, 'hr') || str_contains($detailCatKey, 'people') => 'bg-[#F5F3FF] text-[#5B21B6]',
        default => 'bg-[#F1F3F7] text-[#5A6478]',
    };
    $stagePillBg = $application->status->color ?? '#0F1F3D';
@endphp
<style>
    .right-panel-box {
        overflow-x: scroll;
        max-height: 34rem;
    }
    .resume-button {
        text-align: center;
        margin-top: 1rem;
    }
</style>

<div class="ja-applicant-detail max-h-[calc(100vh-0px)] overflow-y-auto bg-[#F8F7F4] font-[family-name:Plus_Jakarta_Sans]">
    <div class="relative bg-gradient-to-br from-[#0F1F3D] to-[#162849] px-5 pb-5 pt-6">
        <button type="button" class="right-side-toggle absolute right-3 top-3 flex h-7 w-7 items-center justify-center rounded-lg border border-white/15 bg-white/[0.07] text-white/80 transition hover:bg-white/10" title="@lang('app.close')" aria-label="@lang('app.close')">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img src="{{ $application->photo_url }}" alt="" class="h-12 w-12 rounded-full border-2 border-white object-cover shadow-lg" width="48" height="48">
        <h2 class="mt-3 text-[17px] font-extrabold tracking-[-0.02em] text-white">{{ ucwords($application->full_name) }}</h2>
        <p class="mt-0.5 text-[12.5px] text-white/50">{{ ucwords($application->job?->title ?? '—') }}</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold text-white" style="background: {{ $stagePillBg }};">{{ ucwords($application->status->status) }}</span>
            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $detailCatClass }}">{{ ucfirst($detailCatName) }}</span>
        </div>
    </div>

    <div class="border-b border-[#F0EEE9] bg-white px-5">
        <div class="flex items-center justify-between border-b border-[#F0EEE9] py-3.5">
            <span class="text-[10.5px] font-bold uppercase tracking-[0.09em] text-[#B0B8C4]">@lang('modules.jobApplication.ratingLabel')</span>
            @if($user->cans('edit_job_applications'))
                <div class="stars stars-example-fontawesome [&_.br-theme-fontawesome-stars]:leading-none">
                    <select id="example-fontawesome" name="rating" autocomplete="off">
                        <option value=""></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
            @else
                <span class="text-[13px] font-semibold text-[#1A1E2E]">{{ $application->rating ?? '—' }}</span>
            @endif
        </div>
        <div class="flex items-center justify-between py-3.5">
            <span class="text-[10.5px] font-bold uppercase tracking-[0.09em] text-[#B0B8C4]">@lang('modules.jobApplication.appliedAt')</span>
            <span class="text-[13px] font-semibold text-[#1A1E2E]">{{ $application->created_at->timezone($global->timezone)->format('d M, Y') }}</span>
        </div>
    </div>

    <div class="flex flex-col gap-2.5 border-b border-[#F0EEE9] bg-white px-5 py-4">
        <div class="flex flex-wrap gap-2" id="resume-{{ $application->id }}">
            {{-- Stage mover --}}
            @if($user->cans('edit_job_applications'))
            <div class="mt-3 pt-3 border-t border-[#F0EEE9]">
                <p class="text-[10.5px] font-bold uppercase tracking-[0.09em] text-[#B0B8C4] mb-2">Move to Stage</p>
                <div class="flex flex-wrap gap-2" id="stage-mover-{{ $application->id }}">
                    @php
                        $allStatuses = \App\ApplicationStatus::orderBy('position')->get();
                        $currentStatusId = $application->status_id;
                    @endphp
                    @foreach($allStatuses as $stageOption)
                        @if($stageOption->id !== $currentStatusId)
                            <button
                                type="button"
                                onclick="jaMoveFromDetail({{ $application->id }}, {{ $stageOption->id }}, '{{ addslashes(ucwords(str_replace('_', ' ', $stageOption->status))) }}')"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11.5px] font-semibold text-white transition hover:opacity-80"
                                style="background: {{ $stageOption->color ?? '#6B7280' }};">
                                {{ ucwords(str_replace('_', ' ', $stageOption->status)) }}
                                <i class="fa fa-arrow-right" style="font-size:9px;"></i>
                            </button>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11.5px] font-semibold text-white ring-2 ring-white ring-offset-1 cursor-default"
                                style="background: {{ $stageOption->color ?? '#6B7280' }}; opacity:1;">
                                <i class="fa fa-check" style="font-size:9px;"></i>
                                {{ ucwords(str_replace('_', ' ', $stageOption->status)) }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
            @if ($application->resume_url)
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
        @if ($user->cans('delete_job_applications'))
            <div class="flex gap-2.5">
                <a href="javascript:archiveApplication({{ $application->id }})"
                   class="inline-flex flex-1 items-center justify-center rounded-[10px] bg-[#ECFEFF] px-3 py-2.5 text-[12.5px] font-semibold text-[#0891B2] transition hover:bg-[#CFFAFE]">
                    <i class="fa fa-archive mr-1.5"></i>
                    @lang('modules.jobApplication.archiveApplication')
                </a>
                <a href="javascript:deleteApplication({{ $application->id }})"
                   class="inline-flex flex-1 items-center justify-center rounded-[10px] bg-[#FFF1F2] px-3 py-2.5 text-[12.5px] font-semibold text-[#EF4444] transition hover:bg-[#FFE4E6]">
                    <i class="fa fa-trash mr-1.5"></i>
                    @lang('modules.jobApplication.deleteApplication')
                </a>
            </div>
        @endif
    </div>

    <div class="space-y-4 p-4 pb-8">
        <!-- Personal Information Section -->
        <div class="bg-white rounded-2xl border border-[#F0EEE9] p-5 mb-4 shadow-[0_1px_3px_rgba(15,31,61,0.04)]">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-[#F0EEE9]">
                <i class="fa fa-user mr-2 text-blue-600"></i>
                @lang('Personal Information')
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('app.name')</label>
                    <p class="text-sm font-medium text-gray-900">{{ ucwords($application->full_name) }}</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('modules.jobApplication.appliedFor')</label>
                    <p class="text-sm font-medium text-gray-900">
                        {{ ucwords($application->job?->title ?? 'N/A') . ' (' . ucwords(optional($application->location)->location ?? 'N/A') . ')' }}
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('app.email')</label>
                    <a href="mailto:{{ $application->email }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">{{ $application->email }}</a>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('app.phone')</label>
                    <a href="tel:{{ $application->phone }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">{{ $application->phone }}</a>
                </div>

                @if (!is_null($application->gender))
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('app.gender')</label>
                        <p class="text-sm font-medium text-gray-900" id="gender-{{ $application->id }}">{{ ucfirst($application->gender) }}</p>
                    </div>
                @endif
                @if (!is_null($application->dob))
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('app.dob')</label>
                        <p class="text-sm font-medium text-gray-900" id="dob-{{ $application->id }}">{{ $application->dob->format('jS F, Y') }}</p>
                    </div>
                @endif
            </div>

            @if (!is_null($application->address))
                <div class="mt-4 bg-gray-50 rounded-lg p-4">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('app.address')</label>
                    <p class="text-sm font-medium text-gray-900" id="address-{{ $application->id }}">{{ $application->address}}</p>
                </div>
            @endif

            @if (!is_null($application->country))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('app.country')</label>
                        <p class="text-sm font-medium text-gray-900" id="country-{{ $application->id }}">{{ $application->country }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('app.state')</label>
                        <p class="text-sm font-medium text-gray-900" id="state-{{ $application->id }}">{{ $application->state }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('app.city')</label>
                        <p class="text-sm font-medium text-gray-900" id="city-{{ $application->id }}">{{ $application->city }}</p>
                    </div>
                </div>
            @endif

            <div class="mt-4 bg-gray-50 rounded-lg p-4">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('modules.jobApplication.appliedAt')</label>
                <p class="text-sm font-medium text-gray-900">
                    <i class="fa fa-calendar mr-2 text-gray-400"></i>
                    {{ $application->created_at->timezone($global->timezone)->format('d M, Y H:i') }}
                </p>
            </div>
        </div>

        @if (!is_null($application->cover_letter))
        <div class="bg-white rounded-2xl border border-[#F0EEE9] p-5 mb-4 shadow-[0_1px_3px_rgba(15,31,61,0.04)]">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-[#F0EEE9]">
                <i class="fa fa-file-text-o mr-2 text-blue-600"></i>
                @lang('modules.jobs.coverLetter')
            </h3>
            <div class="prose max-w-none">
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $application->cover_letter }}</p>
            </div>
        </div>
        @endif
        @if(count($answers) > 0)
        <div class="bg-white rounded-2xl border border-[#F0EEE9] p-5 mb-4 shadow-[0_1px_3px_rgba(15,31,61,0.04)]">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-[#F0EEE9]">
                <i class="fa fa-question-circle mr-2 text-blue-600"></i>
                @lang('modules.front.additionalDetails')
            </h3>
            <div class="space-y-4">
                @forelse($answers as $answer)
                    <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-blue-500">
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">{{$answer->question->question}}</h4>
                     @if($answer->question->type == 'text')
                        <p class="text-sm text-gray-700">{{ ucfirst($answer->answer)}}</p>
                    @elseif($answer->question->type == 'radio')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                            {{ strtolower($answer->answer) == 'yes' ? 'bg-green-100 text-green-800' : 
                               (strtolower($answer->answer) == 'no' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                            <i class="fa fa-dot-circle-o mr-1.5"></i>
                            {{ ucfirst($answer->answer) }}
                        </span>
                    @else
                        @if(!is_null($answer->file))
                        <a target="_blank" href="{{ $answer->file_url }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm font-medium transition-colors">
                            <i class="fa fa-file-o mr-2"></i>
                            @lang('app.view') @lang('app.file')
                        </a>
                        @endif
                    @endif
                    </div>
                @empty
                @endforelse
            </div>
        </div>
        @endif
        @if(!is_null($application->schedule))
        <div class="bg-white rounded-2xl border border-[#F0EEE9] p-5 mb-4 shadow-[0_1px_3px_rgba(15,31,61,0.04)]">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-[#F0EEE9]">
                <i class="fa fa-calendar-check-o mr-2 text-blue-600"></i>
                @lang('modules.interviewSchedule.scheduleDetail')
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('modules.interviewSchedule.scheduleDate')</label>
                    <p class="text-sm font-medium text-gray-900">
                        <i class="fa fa-clock-o mr-2 text-gray-400"></i>
                        {{ $application->schedule->schedule_date->format('d M, Y H:i') }}
                    </p>
                </div>
                @if($zoom_setting->enable_zoom == 1)
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">@lang('modules.interviewSchedule.interviewType')</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $application->schedule->interview_type == 'online' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        <i class="fa {{ $application->schedule->interview_type == 'online' ? 'fa-video-camera' : 'fa-building' }} mr-2"></i>
                        {{ $application->schedule->interview_type == 'online' ? 'Online' : 'Offline' }}
                    </span>
                </div>
                @endif
            </div>
            
            @if(count($application->schedule->employee) > 0)
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">@lang('modules.interviewSchedule.assignedEmployee')</h4>
                <div class="space-y-3">
                    @forelse($application->schedule->employee as $key => $emp )
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                    <i class="fa fa-user text-blue-600"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-900">{{ ucwords($emp->user->name) }}</p>
                            </div>
                            <div>
                                @if($emp->user_accept_status == 'accept')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fa fa-check-circle mr-1"></i>
                                        {{ ucwords($emp->user_accept_status) }}
                                    </span>
                                @elseif($emp->user_accept_status == 'refuse')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fa fa-times-circle mr-1"></i>
                                        {{ ucwords($emp->user_accept_status) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fa fa-clock-o mr-1"></i>
                                        {{ ucwords($emp->user_accept_status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
            @endif
        </div>
        @endif

        @if(isset($application->schedule->comments) == 'interview' && count($application->schedule->comments) > 0)
        <div class="bg-white rounded-2xl border border-[#F0EEE9] p-5 mb-4 shadow-[0_1px_3px_rgba(15,31,61,0.04)]">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-[#F0EEE9]">
                <i class="fa fa-comments mr-2 text-blue-600"></i>
                @lang('modules.interviewSchedule.comments')
            </h3>
            <div class="space-y-3">
                @forelse($application->schedule->comments as $key => $comment )
                    <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-blue-500">
                        <div class="flex items-center mb-2">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                                <i class="fa fa-user text-blue-600 text-xs"></i>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">{{$comment->user->name }}</span>
                        </div>
                        <p class="text-sm text-gray-700 ml-10">{{ $comment->comment }}</p>
                    </div>
                @empty
                @endforelse
            </div>
        </div>
        @endif

        @if(!is_null($application->skype_id))
        <div class="bg-white rounded-2xl border border-[#F0EEE9] p-5 mb-4 shadow-[0_1px_3px_rgba(15,31,61,0.04)]">
            <div class="flex flex-wrap gap-3">
                <span class="skype-button rounded" data-contact-id="live:{{$application->skype_id}}"
                      data-text="Call"></span>
            </div>
        </div>
        @endif
        @if ($user->cans('edit_job_applications'))
        <div class="bg-white rounded-2xl border border-[#F0EEE9] p-5 mb-4 shadow-[0_1px_3px_rgba(15,31,61,0.04)]" id="skills-container">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-[#F0EEE9]">
                <i class="fa fa-star mr-2 text-blue-600"></i>
                @lang('modules.jobApplication.skills')
            </h3>
            <div class="mb-4">
                <select name="skills[]" id="skills" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 select2" multiple>
                    @forelse ($skills as $skill)
                        <option @if (!is_null($application->skills) && in_array($skill->id, $application->skills)) selected @endif value="{{ $skill->id }}">{{ $skill->name }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
            <a href="javascript:addSkills({{ $application->id}});" id="add-skills" 
               class="px-4 py-2.5 border-2 border-green-600 text-green-600 rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-sm font-medium inline-flex items-center transition-colors">
                <i class="fa fa-plus mr-2"></i>
                @if (!is_null($application->skills) && sizeof($application->skills) > 0)
                    @lang('modules.jobApplication.updateSkills')
                @else
                    @lang('modules.jobApplication.addSkills')
                @endif
            </a>
        </div>
        @endif

        <div class="bg-white rounded-2xl border border-[#F0EEE9] p-5 mb-4 shadow-[0_1px_3px_rgba(15,31,61,0.04)]">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-[#F0EEE9]">
                <i class="fa fa-sticky-note mr-2 text-blue-600"></i>
                @lang('modules.jobApplication.applicantNotes')
            </h3>

            <div id="applicant-notes" class="space-y-4 mb-6">
                @foreach($application->notes as $key => $notes )
                    <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-blue-500" id="note-{{ $notes->id }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                                        <i class="fa fa-user text-blue-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-semibold text-gray-900">{{ ucwords($notes->user->name) }}</h6>
                                        <span class="text-xs text-gray-500">{{ $notes->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700 note-text ml-10 mt-2">{{ ucfirst($notes->note_text) }}</p>
                                <div class="note-textarea"></div>
                            </div>
                            @if($user->cans('edit_job_applications'))
                                <div class="flex items-center gap-2 ml-4">
                                    <a href="javascript:;" class="edit-note p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" data-note-id="{{ $notes->id }}" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="javascript:;" class="delete-note p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" data-note-id="{{ $notes->id }}" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($user->cans('edit_job_applications'))
                <div class="border-t border-gray-200 pt-4">
                    <div class="mb-3">
                        <textarea name="note" id="note_text" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                  placeholder="@lang('modules.jobApplication.addNote')"></textarea>
                    </div>
                    <a href="javascript:;" id="add-note" 
                       class="px-4 py-2.5 border-2 border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm font-medium inline-flex items-center transition-colors">
                        <i class="fa fa-plus mr-2"></i>
                        @lang('modules.jobApplication.addNote')
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@if($user->cans('edit_job_applications'))
    <script src="{{ asset('assets/plugins/jquery-bar-rating-master/dist/jquery.barrating.min.js') }}"
            type="text/javascript"></script>
    <script>
        function jaMoveFromDetail(appId, toStatusId, toStatusLabel) {
        var token = '{{ csrf_token() }}';
        var url = '{{ route("admin.job-applications.bulk-status-update") }}';

        swal({
            title: 'Move to ' + toStatusLabel + '?',
            text: 'This applicant will be moved to the ' + toStatusLabel + ' stage.',
            type: 'info',
            showCancelButton: true,
            confirmButtonColor: '#2563EB',
            confirmButtonText: 'Yes, move',
            cancelButtonText: 'Cancel',
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm) {
            if (isConfirm) {
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: { _token: token, ids: [appId], status_id: toStatusId },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Reload the sidebar with updated data
                            var showUrl = "{{ route('admin.job-applications.show', ':id') }}".replace(':id', appId);
                            $.easyAjax({
                                type: 'GET',
                                url: showUrl,
                                success: function(res) {
                                    if (res.status === 'success') {
                                        $('#right-sidebar-content').html(res.view);
                                    }
                                }
                            });
                            // Refresh table if it exists
                            if (typeof table !== 'undefined') {
                                table.draw(false);
                            }
                            if (typeof jaLoadTabCounts === 'function') {
                                jaLoadTabCounts();
                            }
                        }
                    }
                });
            }
        });
    }
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

    function deleteApplication(applicationId) {
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

                var url = "{{ route('admin.job-applications.destroy', ':id') }}";
                url = url.replace(':id', applicationId);

                var token = '{{ csrf_token() }}';

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token':token, '_method': 'DELETE'},
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

    function archiveApplication(applicationId) {
        swal({
            title: "@lang('errors.areYouSure')",
            text: "@lang('errors.archiveWarning')",
            type: "info",
            showCancelButton: true,
            confirmButtonColor: "#28A745",
            confirmButtonText: "@lang('app.yes')",
            cancelButtonText: "@lang('app.no')",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm){
            if (isConfirm) {

                var url = "{{ route('admin.job-applications.archiveJobApplication', ':id') }}";
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