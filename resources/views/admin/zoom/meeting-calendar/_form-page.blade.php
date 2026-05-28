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
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2" style="font-weight:600">
                                                @lang('modules.zoommeeting.meetingName') <span class="text-red-500 normal-case tracking-normal">*</span>
                                            </label>
                                            <input type="text"
                                                   name="meeting_title"
                                                   value="{{ old('meeting_title', optional($meeting)->meeting_name) }}"
                                                   class="w-full h-11 px-4 text-sm text-slate-800 placeholder-slate-300 bg-transparent border border-slate-200 rounded-xl focus:border-[#3b82f6] focus:ring-2 focus:ring-blue-500/10 transition">
                                        </div>

                                        <div class="field-focus">
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2" style="font-weight:600">
                                                @lang('app.category')
                                            </label>
                                            <div class="flex gap-2">
                                                <div class="flex-1 relative border border-slate-200 rounded-xl overflow-hidden transition-all duration-200">
                                                    <select id="category_id"
                                                            name="category_id"
                                                            class="w-full h-11 px-4 pr-9 text-sm text-slate-700 bg-white appearance-none cursor-pointer">
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
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2" style="font-weight:600">
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
                                            <div class="border border-slate-200 rounded-xl overflow-hidden transition-all duration-200">
                                                <input type="text" name="start_date" id="start_date" value="{{ $startDate }}" class="w-full h-11 px-4 text-sm text-slate-700 bg-white">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-1.5">@lang('modules.zoommeeting.startTime')</label>
                                            <div class="border border-slate-200 rounded-xl overflow-hidden transition-all duration-200">
                                                <input type="text" name="start_time" id="start_time" value="{{ $startTime }}" class="w-full h-11 px-4 text-sm text-slate-700 bg-white">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- End --}}
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-1.5">
                                                @lang('modules.zoommeeting.endOn') <span class="text-red-500">*</span>
                                            </label>
                                            <div class="border border-slate-200 rounded-xl overflow-hidden transition-all duration-200">
                                                <input type="text" name="end_date" id="end_date" value="{{ $endDate }}" class="w-full h-11 px-4 text-sm text-slate-700 bg-white">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-slate-400 mb-1.5">@lang('modules.zoommeeting.endTime')</label>
                                            <div class="border border-slate-200 rounded-xl overflow-hidden transition-all duration-200">
                                                <input type="text" name="end_time" id="end_time" value="{{ $endTime }}" class="w-full h-11 px-4 text-sm text-slate-700 bg-white">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Attendees --}}
                        @if (! $isOccurrence)
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
                        @endif

                        {{-- Section: Options (Repeat + Reminder) --}}
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-100 overflow-hidden fade-up">
                            <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center">
                                        <i class="fa fa-repeat text-slate-500"></i>
                                    </div>
                                    <h2 class="text-sm font-bold text-slate-700">Options</h2>
                                </div>
                            </div>

                            <div class="px-6 py-5 space-y-4">
                                @if (! $isOccurrence)
                                    <div class="flex items-center justify-between py-2">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox"
                                                   value="1"
                                                   name="repeat"
                                                   id="repeat-meeting"
                                                   class="w-4 h-4 accent-blue-600 rounded border-gray-300 text-primary focus:ring-primary"
                                                   @if($repeatChecked) checked @endif
                                                   @if($repeatDisabled) disabled @endif>
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

                                    <div id="repeat-fields" class="grid grid-cols-1 gap-2 md:grid-cols-3" style="{{ $repeatChecked ? '' : 'display: none;' }}">
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
                                @endif

                                <div class="flex items-center justify-between py-2">
                                    <div class="flex items-center gap-3">
                                        <input type="hidden" name="send_reminder" value="0">
                                        <input type="checkbox"
                                               value="1"
                                               name="send_reminder"
                                               id="send_reminder"
                                               class="w-4 h-4 accent-blue-600 rounded border-gray-300 text-primary focus:ring-primary"
                                               @if($sendReminderChecked) checked @endif>
                                        <div>
                                            <p class="text-sm font-medium text-slate-800">{{ __('modules.zoommeeting.reminder') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div id="reminder-fields" class="grid grid-cols-1 gap-2 md:grid-cols-2" style="{{ $sendReminderChecked ? '' : 'display: none;' }}">
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
                        @if (! $isOccurrence)
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
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2.5" style="font-weight:600">
                                            @lang('modules.zoommeeting.hostVideoStatus')
                                        </p>
                                        <div class="flex items-center gap-2 p-1 bg-slate-50 rounded-xl border border-slate-100">
                                            <label class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg cursor-pointer text-xs font-semibold text-slate-500 hover:text-slate-700">
                                                <input type="radio" name="host_video" value="1" class="sr-only" @if(old('host_video', optional($meeting)->host_video) == 1) checked @endif>
                                                <i class="fa fa-check text-blue-600" aria-hidden="true"></i>
                                                @lang('app.enable')
                                            </label>
                                            <label class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg cursor-pointer text-xs font-semibold text-slate-500 hover:text-slate-700">
                                                <input type="radio" name="host_video" value="0" class="sr-only" @if(old('host_video', optional($meeting)->host_video ?? 0) == 0) checked @endif>
                                                <i class="fa fa-times text-red-600" aria-hidden="true"></i>
                                                @lang('app.disable')
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2.5" style="font-weight:600">
                                            @lang('modules.zoommeeting.participantVideoStatus')
                                        </p>
                                        <div class="flex items-center gap-2 p-1 bg-slate-50 rounded-xl border border-slate-100">
                                            <label class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg cursor-pointer text-xs font-semibold text-slate-500 hover:text-slate-700">
                                                <input type="radio" name="participant_video" value="1" class="sr-only" @if(old('participant_video', optional($meeting)->participant_video) == 1) checked @endif>
                                                <i class="fa fa-check text-blue-600" aria-hidden="true"></i>
                                                @lang('app.enable')
                                            </label>
                                            <label class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg cursor-pointer text-xs font-semibold text-slate-500 hover:text-slate-700">
                                                <input type="radio" name="participant_video" value="0" class="sr-only" @if(old('participant_video', optional($meeting)->participant_video ?? 0) == 0) checked @endif>
                                                <i class="fa fa-times text-red-600" aria-hidden="true"></i>
                                                @lang('app.disable')
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Meeting Host --}}
                        @if (! $isOccurrence)
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
                                        <select class="w-full h-11 px-4 pr-9 text-sm text-slate-700 bg-transparent appearance-none cursor-pointer"
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
                        @endif

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
