@extends('layouts.app')

@section('hide-ra-page-header')
@endsection

@section('page-title-html')
<div>
    <h1 class="text-[22px] font-extrabold tracking-tight text-[#0F1F3D]">
        @lang('app.createNew') <span class="text-[20px] font-normal italic text-[#2563EB]" style="font-family:'Instrument Serif',serif;">@lang('modules.zoommeeting.meetingName')</span>
    </h1>
</div>
@endsection

@section('page-subtitle')
<p class="mt-1 text-[12.5px] text-[#8892A0]">@lang('modules.zoommeeting.addMeeting')</p>
@endsection

@push('head-script')
@include('admin.jobs.partials.job-form-head')
@endpush

@section('content')
    <div class="min-h-screen bg-gray-50">
        {{-- Top Nav --}}
        {{-- <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
            <div class="max-w-5xl mx-auto px-6 h-14 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.zoom-meeting.index') }}"
                       class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 transition-colors">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <div class="h-5 w-px bg-gray-200"></div>
                    <nav class="flex items-center gap-1.5 text-sm text-gray-400">
                        <a href="{{ route('admin.zoom-meeting.index') }}"
                           class="hover:text-gray-700 transition-colors">@lang('menu.zoom')</a>
                        <i class="fa fa-angle-right text-[10px] text-gray-300" aria-hidden="true"></i>
                        <span class="text-gray-700 font-medium">@lang('modules.zoommeeting.addMeeting')</span>
                    </nav>
                </div>
                <span class="text-xs text-slate-400 bg-slate-100 px-3 py-1 rounded-full font-medium">
                    Draft
                </span>
            </div>
        </header> --}}

        <main class="max-w-5xl mx-auto px-6 py-10">
            {{-- Page Heading --}}
            {{-- <div class="mb-8">
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-200">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.361a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-800 text-slate-900 tracking-tight leading-none">
                            @lang('app.createNew') <span class="text-blue-600 italic font-semibold">@lang('modules.zoommeeting.meetingName')</span>
                        </h1>
                        <p class="text-sm text-slate-400 mt-0.5">@lang('modules.zoommeeting.addMeeting')</p>
                    </div>
                </div>
            </div> --}}

            {{-- Flash Errors --}}
            @if ($errors->any())
                <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $submitRoute = route('admin.zoom-meeting.store');
            @endphp

            {{-- Inline complete form (no partial include) --}}
            @php
                $meeting = $meeting ?? null;
                $isEdit = $isEdit ?? false;
                $isOccurrence = $isOccurrence ?? false;
                $repeatValue = old('repeat', optional($meeting)->repeat);
                $repeatChecked = ! $isOccurrence && (string) $repeatValue === '1';
                $sendReminderValue = old('send_reminder', optional($meeting)->send_reminder);
                // Occurrence edit should also allow editing reminder fields.
                $sendReminderChecked = (string) $sendReminderValue === '1';
                // Allow repeat on edit as well (fields will still be hidden on occurrence edit).
                $repeatDisabled = false;

                $dateFormat = $isOccurrence ? $global->date_format : 'Y-m-d';
                $startDate = old('start_date', $meeting ? $meeting->start_date_time->format($dateFormat) : request('start_date', now()->format('Y-m-d')));
                $endDate = old('end_date', $meeting ? $meeting->end_date_time->format($dateFormat) : request('end_date', now()->format('Y-m-d')));
                $startTime = old('start_time', $meeting ? $meeting->start_date_time->format('H:i') : now()->format('H:i'));
                $endTime = old('end_time', $meeting ? $meeting->end_date_time->format('H:i') : now()->addHour()->format('H:i'));
            @endphp

            <div class="job-form-page pb-8">
                <form class="ajax-form" method="POST" action="{{ $submitRoute }}" id="createForm">
                    @csrf
                    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-100 overflow-hidden">
                        <div class="p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                {{-- LEFT: Main Form --}}
                                <div class="lg:col-span-2 space-y-5">
                                    {{-- Section: Basic Info --}}
                                    @if (! $isOccurrence)
                                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-100 overflow-hidden fade-up">
                                            <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-6 h-6 rounded-md bg-blue-50 flex items-center justify-center">
                                                        <i class="icon-film text-blue-600"></i>
                                                    </div>
                                                    <h2 class="text-sm font-bold text-slate-700">{{ __('modules.zoommeeting.meetingName') }}</h2>
                                                </div>
                                            </div>

                                            <div class="px-6 py-5 space-y-4">
                                                {{-- Title + Category --}}
                                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                    <div class="field-focus">
                                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">
                                                            @lang('modules.zoommeeting.meetingName') <span class="text-red-500 normal-case tracking-normal">*</span>
                                                        </label>
                                                        <input type="text"
                                                               name="meeting_title"
                                                               value="{{ old('meeting_title', optional($meeting)->meeting_name) }}"
                                                               class="w-full h-11 px-4 text-sm text-slate-800 placeholder-slate-300 bg-transparent border border-slate-200 rounded-xl focus:border-[#3b82f6] focus:ring-2 focus:ring-blue-500/10 transition">
                                                    </div>

                                                    <div class="field-focus">
                                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">
                                                            @lang('app.category')
                                                        </label>
                                                        <div class="flex gap-2">
                                                            <div class="flex-1 relative border border-slate-200 rounded-xl overflow-hidden transition-all duration-200">
                                                                <select id="category_id"
                                                                        name="category_id"
                                                                        class="w-full h-11 px-4 pr-9 text-sm text-slate-700 bg-white appearance-none cursor-pointer border-0 shadow-none focus:ring-0 focus:shadow-none focus:outline-none">
                                                                    <option value="">@lang('modules.message.pleaseSelectCategory')</option>
                                                                    @foreach($categories as $category)
                                                                        <option value="{{ $category->id }}" @if(old('category_id', optional($meeting)->category_id) == $category->id) selected @endif>
                                                                            {{ ucwords($category->category_name) }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <i class="fa fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                                            </div>

                                                            <button type="button"
                                                                    id="addCategory"
                                                                    class="w-11 h-11 flex items-center justify-center rounded-xl bg-blue-50 border border-blue-200 text-blue-600 hover:bg-blue-100 transition-colors flex-shrink-0"
                                                                    title="@lang('app.createNew') @lang('app.category')">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Description --}}
                                                <div class="field-focus">
                                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">
                                                        @lang('modules.zoommeeting.description')
                                                        <span class="normal-case font-normal text-slate-400">(optional)</span>
                                                    </label>
                                                    <textarea name="description"
                                                              rows="3"
                                                              class="w-full px-4 py-3 text-sm text-slate-800 placeholder-slate-300 bg-transparent resize-none border border-slate-200 rounded-xl focus:border-[#3b82f6] focus:ring-2 focus:ring-blue-500/10 transition">{{ old('description', optional($meeting)->description) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Section: Schedule --}}
                                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-100 overflow-hidden fade-up">
                                        <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-6 h-6 rounded-md bg-violet-50 flex items-center justify-center">
                                                    <i class="fa fa-calendar text-violet-600"></i>
                                                </div>
                                                <h2 class="text-sm font-bold text-slate-700">{{ __('modules.zoommeeting.startOn') }}</h2>
                                            </div>
                                        </div>

                                        <div class="px-6 py-5">
                                            <div class="grid grid-cols-2 gap-5">
                                                {{-- Start --}}
                                                <div class="space-y-3">
                                                    <div>
                                                        <label class="block text-xs text-slate-400 mb-1.5">
                                                            @lang('modules.zoommeeting.startOn') <span class="text-red-500">*</span>
                                                        </label>
                                                        <div class="relative border border-slate-200 rounded-xl overflow-hidden transition-all duration-200">
                                                            <input type="text" name="start_date" id="start_date" value="{{ $startDate }}" class="w-full h-11 px-4 pr-10 text-sm text-slate-700 bg-white border-0 shadow-none focus:ring-0 focus:shadow-none focus:outline-none">
                                                            <button type="button" id="start_date_trigger" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" aria-label="@lang('modules.zoommeeting.startOn')">
                                                                <i class="fa fa-calendar"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-slate-400 mb-1.5">@lang('modules.zoommeeting.startTime')</label>
                                                        <div class="relative border border-slate-200 rounded-xl overflow-hidden transition-all duration-200">
                                                            <input type="time" name="start_time" id="start_time" value="{{ $startTime }}" class="w-full h-11 px-4 pr-10 text-sm text-slate-700 bg-white border-0 shadow-none focus:ring-0 focus:shadow-none focus:outline-none">
                                                            <button type="button" id="start_time_trigger" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" aria-label="@lang('modules.zoommeeting.startTime')">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- End --}}
                                                <div class="space-y-3">
                                                    <div>
                                                        <label class="block text-xs text-slate-400 mb-1.5">
                                                            @lang('modules.zoommeeting.endOn') <span class="text-red-500">*</span>
                                                        </label>
                                                        <div class="relative border border-slate-200 rounded-xl overflow-hidden transition-all duration-200">
                                                            <input type="text" name="end_date" id="end_date" value="{{ $endDate }}" class="w-full h-11 px-4 pr-10 text-sm text-slate-700 bg-white border-0 shadow-none focus:ring-0 focus:shadow-none focus:outline-none">
                                                            <button type="button" id="end_date_trigger" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" aria-label="@lang('modules.zoommeeting.endOn')">
                                                                <i class="fa fa-calendar"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-slate-400 mb-1.5">@lang('modules.zoommeeting.endTime')</label>
                                                        <div class="relative border border-slate-200 rounded-xl overflow-hidden transition-all duration-200">
                                                            <input type="time" name="end_time" id="end_time" value="{{ $endTime }}" class="w-full h-11 px-4 pr-10 text-sm text-slate-700 bg-white border-0 shadow-none focus:ring-0 focus:shadow-none focus:outline-none">
                                                            <button type="button" id="end_time_trigger" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" aria-label="@lang('modules.zoommeeting.endTime')">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section: Attendees --}}
                                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-100 overflow-hidden fade-up">
                                        <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-6 h-6 rounded-md bg-teal-50 flex items-center justify-center">
                                                    <i class="fa fa-user text-teal-600"></i>
                                                </div>
                                                <h2 class="text-sm font-bold text-slate-700">{{ __('modules.meetings.addEmployees') }}</h2>
                                            </div>
                                        </div>
                                        <div class="px-6 py-5">
                                            <select class="job-form-sel form-control select2 select2-multiple w-full"
                                                    id="employee_id"
                                                    multiple="multiple"
                                                    data-placeholder="@lang('modules.message.chooseMember')"
                                                    name="employee_id[]">
                                                @foreach($employees as $emp)
                                                    <option value="{{ $emp->id }}" @if($meeting && in_array($emp->id, $meeting->attendees->pluck('id')->toArray())) selected @endif>
                                                        {{ ucwords($emp->name) }} @if($emp->id == $user->id) (YOU) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Section: Options (Repeat + Reminder) --}}
                                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-100 overflow-hidden fade-up">
                                        <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center">
                                                    <i class="fa fa-repeat text-slate-500"></i>
                                                </div>
                                                <h2 class="text-sm font-bold text-slate-700">@lang('app.options')
                                                    <span class="text-xs text-slate-400">(@lang('app.optional'))</span>
                                                </h2>
                                            </div>
                                        </div>

                                        <div class="px-6 py-5 space-y-4">
                                            <div class="flex items-center justify-between py-2">
                                                <div class="flex items-center gap-3">
                                                    <label for="repeat-meeting" class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox"
                                                               value="1"
                                                               name="repeat"
                                                               id="repeat-meeting"
                                                               class="peer sr-only"
                                                               @if($repeatChecked) checked @endif
                                                               @if($repeatDisabled) disabled @endif>
                                                        <span class="w-4 h-4 rounded border border-slate-300 bg-white inline-flex items-center justify-center transition peer-checked:bg-blue-600 peer-checked:border-blue-600 peer-disabled:bg-slate-100 peer-disabled:border-slate-200">
                                                            <i class="fa fa-check text-[10px] text-white opacity-0 peer-checked:opacity-100"></i>
                                                        </span>
                                                    </label>
                                                    <div>
                                                        <p class="text-sm font-medium text-slate-800">{{ __('modules.zoommeeting.repeat') }}</p>
                                                        @if($repeatDisabled)
                                                            <p class="text-xs text-slate-400">({{ __('app.edit') }})</p>
                                                        @else
                                                            <p class="text-xs text-slate-400">{{ __('modules.zoommeeting.cyclesToolTip') }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="h-px bg-slate-50"></div>

                                            <div id="repeat-fields" class="grid grid-cols-1 gap-2 md:grid-cols-3 {{ $repeatChecked ? '' : 'hidden' }}">
                                                <input type="number"
                                                       min="1"
                                                       value="{{ old('repeat_every', optional($meeting)->repeat_every ?? 1) }}"
                                                       name="repeat_every"
                                                       class="job-form-sel form-control h-11 rounded-xl border border-slate-200 px-4"
                                                       @if($repeatDisabled) disabled @endif>
                                                <select name="repeat_type"
                                                        class="job-form-sel form-control h-11 rounded-xl border border-slate-200 px-4 bg-white"
                                                        @if($repeatDisabled) disabled @endif>
                                                    <option value="day" @if(old('repeat_type', optional($meeting)->repeat_type) == 'day') selected @endif>@lang('modules.zoommeeting.day')</option>
                                                    <option value="week" @if(old('repeat_type', optional($meeting)->repeat_type) == 'week') selected @endif>@lang('modules.zoommeeting.week')</option>
                                                    <option value="month" @if(old('repeat_type', optional($meeting)->repeat_type) == 'month') selected @endif>@lang('modules.zoommeeting.month')</option>
                                                </select>
                                                <input type="text"
                                                       name="repeat_cycles"
                                                       value="{{ old('repeat_cycles', optional($meeting)->repeat_cycles) }}"
                                                       class="job-form-sel form-control h-11 rounded-xl border border-slate-200 px-4"
                                                       placeholder="@lang('modules.zoommeeting.cycles')"
                                                       @if($repeatDisabled) disabled @endif>
                                            </div>

                                            <div class="h-px bg-slate-50"></div>

                                            <div class="flex items-center justify-between py-2">
                                                <div class="flex items-center gap-3">
                                                    <input type="hidden" name="send_reminder" value="0">
                                                    <label for="send_reminder" class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox"
                                                               value="1"
                                                               name="send_reminder"
                                                               id="send_reminder"
                                                               class="peer sr-only"
                                                               @if($sendReminderChecked) checked @endif>
                                                        <span class="w-4 h-4 rounded border border-slate-300 bg-white inline-flex items-center justify-center transition peer-checked:bg-blue-600 peer-checked:border-blue-600">
                                                            <i class="fa fa-check text-[10px] text-white opacity-0 peer-checked:opacity-100"></i>
                                                        </span>
                                                    </label>
                                                    <div>
                                                        <p class="text-sm font-medium text-slate-800">{{ __('modules.zoommeeting.reminder') }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="reminder-fields" class="grid grid-cols-1 gap-2 md:grid-cols-2 {{ $sendReminderChecked ? '' : 'hidden' }}">
                                                <input type="number"
                                                       min="1"
                                                       value="{{ old('remind_time', optional($meeting)->remind_time ?? 1) }}"
                                                       name="remind_time"
                                                       class="job-form-sel form-control h-11 rounded-xl border border-slate-200 px-4"
                                                       placeholder="@lang('modules.zoommeeting.remindBefore')">
                                                <select name="remind_type"
                                                        class="job-form-sel form-control h-11 rounded-xl border border-slate-200 px-4 bg-white">
                                                    <option value="day" @if(old('remind_type', optional($meeting)->remind_type) == 'day') selected @endif>@lang('modules.zoommeeting.day')</option>
                                                    <option value="hour" @if(old('remind_type', optional($meeting)->remind_type) == 'hour') selected @endif>@lang('modules.zoommeeting.hour')</option>
                                                    <option value="minute" @if(old('remind_type', optional($meeting)->remind_type) == 'minute') selected @endif>@lang('modules.zoommeeting.minute')</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- RIGHT: Sidebar --}}
                                <div class="space-y-5">
                                    {{-- Video Settings --}}
                                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-100 overflow-hidden fade-up">
                                        <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-6 h-6 rounded-md bg-orange-50 flex items-center justify-center">
                                                    <i class="fa fa-video text-orange-500"></i>
                                                </div>
                                                <h2 class="text-sm font-bold text-slate-700">{{ __('modules.zoommeeting.meetingDetails') }}</h2>
                                            </div>
                                        </div>

                                        <div class="px-6 py-5 space-y-4">
                                            <div>
                                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2.5">
                                                    @lang('modules.zoommeeting.hostVideoStatus')
                                                </p>
                                                @php $hostVideoChecked = old('host_video', optional($meeting)->host_video ?? 0) == 1; @endphp
                                                <div class="flex items-center justify-between gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                                    <div class="flex items-center gap-2 text-sm font-semibold">
                                                        <i id="host_video_icon" class="fa {{ $hostVideoChecked ? 'fa-check text-blue-600' : 'fa-times text-red-600' }}" aria-hidden="true"></i>
                                                        <span id="host_video_state" class="{{ $hostVideoChecked ? 'text-blue-700' : 'text-red-600' }}">
                                                            {{ $hostVideoChecked ? __('app.enable') : __('app.disable') }}
                                                        </span>
                                                    </div>
                                                    <input type="hidden" id="host_video_value" name="host_video" value="{{ $hostVideoChecked ? 1 : 0 }}">
                                                    <button type="button" id="host_video_switch" class="relative inline-flex h-7 w-12 cursor-pointer items-center rounded-full transition bg-slate-300">
                                                        <span id="host_video_thumb" class="inline-block h-5 w-5 rounded-full bg-white shadow transition translate-x-1"></span>
                                                    </button>
                                                </div>
                                            </div>

                                            <div>
                                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2.5">
                                                    @lang('modules.zoommeeting.participantVideoStatus')
                                                </p>
                                                @php $participantVideoChecked = old('participant_video', optional($meeting)->participant_video ?? 0) == 1; @endphp
                                                <div class="flex items-center justify-between gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                                    <div class="flex items-center gap-2 text-sm font-semibold">
                                                        <i id="participant_video_icon" class="fa {{ $participantVideoChecked ? 'fa-check text-blue-600' : 'fa-times text-red-600' }}" aria-hidden="true"></i>
                                                        <span id="participant_video_state" class="{{ $participantVideoChecked ? 'text-blue-700' : 'text-red-600' }}">
                                                            {{ $participantVideoChecked ? __('app.enable') : __('app.disable') }}
                                                        </span>
                                                    </div>
                                                    <input type="hidden" id="participant_video_value" name="participant_video" value="{{ $participantVideoChecked ? 1 : 0 }}">
                                                    <button type="button" id="participant_video_switch" class="relative inline-flex h-7 w-12 cursor-pointer items-center rounded-full transition bg-slate-300">
                                                        <span id="participant_video_thumb" class="inline-block h-5 w-5 rounded-full bg-white shadow transition translate-x-1"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Meeting Host --}}
                                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-100 overflow-hidden fade-up">
                                        <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-6 h-6 rounded-md bg-blue-50 flex items-center justify-center">
                                                    <i class="fa fa-user text-blue-600"></i>
                                                </div>
                                                <h2 class="text-sm font-bold text-slate-700">{{ __('modules.zoommeeting.meetingHost') }}</h2>
                                            </div>
                                        </div>

                                        <div class="px-6 py-5">
                                            <div class="border border-slate-200 rounded-xl overflow-hidden relative">
                                                <select class="w-full h-11 px-4 pr-9 text-sm text-slate-700 bg-transparent appearance-none cursor-pointer border-0 shadow-none focus:ring-0 focus:shadow-none focus:outline-none"
                                                        name="created_by">
                                                    @foreach($employees as $emp)
                                                        <option value="{{ $emp->id }}" @if(old('created_by', optional($meeting)->created_by ?? $user->id) == $emp->id) selected @endif>
                                                            {{ ucwords($emp->name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <i class="fa fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex flex-col gap-2.5 pt-1">
                                        <button type="button"
                                                id="zoom-meeting-submit"
                                                data-redirect-url="{{ route('admin.zoom-meeting.index') }}"
                                                class="w-full flex items-center justify-center gap-2.5 h-12 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white text-sm font-bold rounded-2xl transition-all shadow-lg shadow-blue-200">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                            @lang('app.submit')
                                        </button>
                                        <a href="{{ route('admin.zoom-meeting.index') }}"
                                           class="w-full flex items-center justify-center h-11 bg-white hover:bg-slate-50 text-slate-600 text-sm font-semibold rounded-2xl border border-slate-200 transition-colors">
                                            @lang('app.cancel')
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection

<div class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/45 p-4" id="categoryModal" role="dialog" aria-labelledby="categoryModalHeading" aria-hidden="true">
    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white shadow-2xl" id="modal-data-application">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4">
            <span class="text-base font-semibold text-slate-800" id="categoryModalHeading">@lang('app.category')</span>
            <button type="button" class="text-slate-400 transition hover:text-slate-700" data-category-close="true" aria-hidden="true">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div class="p-6" id="categoryModalBody">
            Loading...
        </div>
    </div>
</div>

@push('footer-script')
<script src="{{ asset('assets/node_modules_files/moment/moment.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/select2/dist/js/select2.full.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/node_modules_files/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}" type="text/javascript"></script>
<script>
    function openCategoryModal() {
        $('#categoryModal').css('display', 'flex').removeClass('hidden').addClass('flex').attr('aria-hidden', 'false');
    }

    function closeCategoryModal() {
        $('#categoryModal').css('display', 'none').removeClass('flex').addClass('hidden').attr('aria-hidden', 'true');
        $('#categoryModalBody').html('Loading...');
    }

    $('#createForm select.select2').select2({
        width: '100%',
        dropdownCssClass: 'job-form-select2-dd',
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    $('#start_date, #end_date').bootstrapMaterialDatePicker({
        time: false,
        clearButton: true,
        minDate: new Date()
    });

    // Use native HTML time inputs for reliable HH:mm values.
    $('#start_date_trigger').on('click', function () {
        $('#start_date').trigger('focus').trigger('click');
    });
    $('#end_date_trigger').on('click', function () {
        $('#end_date').trigger('focus').trigger('click');
    });
    function openTimePicker(inputId) {
        var input = document.getElementById(inputId);
        if (!input) {
            return;
        }
        input.focus();
        if (typeof input.showPicker === 'function') {
            input.showPicker();
        } else {
            $(input).trigger('click');
        }
    }
    $('#start_time_trigger').on('click', function () {
        openTimePicker('start_time');
    });
    $('#end_time_trigger').on('click', function () {
        openTimePicker('end_time');
    });

    // Native checkbox toggle using Tailwind classes (no inline display styles)
    $('#repeat-meeting').on('change', function () {
        $('#repeat-fields').toggleClass('hidden', !this.checked);
    });
    $('#send_reminder').on('change', function () {
        $('#reminder-fields').toggleClass('hidden', !this.checked);
    });

    $('#repeat-fields').toggleClass('hidden', !$('#repeat-meeting').is(':checked'));
    $('#reminder-fields').toggleClass('hidden', !$('#send_reminder').is(':checked'));

    function updateVideoSwitchState(valueId, stateId, iconId, trackId, thumbId) {
        var isChecked = $(valueId).val() == '1';
        $(stateId).text(isChecked ? "@lang('app.enable')" : "@lang('app.disable')");
        $(stateId)
            .toggleClass('text-blue-700', isChecked)
            .toggleClass('text-red-600', !isChecked);
        $(iconId)
            .toggleClass('fa-check text-blue-600', isChecked)
            .toggleClass('fa-times text-red-600', !isChecked);
        $(trackId)
            .toggleClass('bg-blue-600', isChecked)
            .toggleClass('bg-slate-300', !isChecked);
        $(thumbId)
            .toggleClass('translate-x-[23px]', isChecked)
            .toggleClass('translate-x-1', !isChecked);
    }

    $('#host_video_switch').on('click', function () {
        var nextValue = $('#host_video_value').val() == '1' ? '0' : '1';
        $('#host_video_value').val(nextValue);
        updateVideoSwitchState('#host_video_value', '#host_video_state', '#host_video_icon', '#host_video_switch', '#host_video_thumb');
    });
    $('#participant_video_switch').on('click', function () {
        var nextValue = $('#participant_video_value').val() == '1' ? '0' : '1';
        $('#participant_video_value').val(nextValue);
        updateVideoSwitchState('#participant_video_value', '#participant_video_state', '#participant_video_icon', '#participant_video_switch', '#participant_video_thumb');
    });
    updateVideoSwitchState('#host_video_value', '#host_video_state', '#host_video_icon', '#host_video_switch', '#host_video_thumb');
    updateVideoSwitchState('#participant_video_value', '#participant_video_state', '#participant_video_icon', '#participant_video_switch', '#participant_video_thumb');

    function getCookie(name) {
        var value = '; ' + document.cookie;
        var parts = value.split('; ' + name + '=');
        if (parts.length === 2) {
            return decodeURIComponent(parts.pop().split(';').shift());
        }
        return '';
    }

    $('#zoom-meeting-submit').on('click', function () {
        var csrfToken = $('#createForm input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content') || '';
        var xsrfToken = getCookie('XSRF-TOKEN');
        var requestData = $('#createForm').serialize();

        $.easyAjax({
            url: $('#createForm').attr('action'),
            container: '#createForm',
            type: 'POST',
            buttonSelector: '#zoom-meeting-submit',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-XSRF-TOKEN': xsrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: requestData,
            success: function (response) {
                if (response.status == 'success') {
                    window.location.href = $('#zoom-meeting-submit').data('redirect-url');
                }
            },
            error: function (xhr) {
                if (xhr && xhr.status === 419) {
                    // Fallback: full submit refreshes session/csrf context
                    $('#createForm').trigger('submit');
                }
            }
        });
    });

    // Category modal - open via ajax
    $(function () {
        closeCategoryModal();
    });

    $('body').on('click', '#addCategory', function (e) {
        e.preventDefault();
        openCategoryModal();
        var url = '{{ route('admin.category.create') }}' + '?inModal=1';
        $.ajaxModal('#categoryModal', url);
    });

    $('body').on('click', '[data-category-close="true"]', function (e) {
        e.preventDefault();
        closeCategoryModal();
    });

    $('body').on('click', '#categoryModal', function (e) {
        if (e.target.id === 'categoryModal') {
            closeCategoryModal();
        }
    });
</script>
@endpush
