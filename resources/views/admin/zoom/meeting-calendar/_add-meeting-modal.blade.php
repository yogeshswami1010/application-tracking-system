<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" id="my-meeting" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl" id="modal-data-application">
        <div class="modal-header flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Schedule Meeting</h2>
                <p class="text-xs text-gray-400 mt-0.5">Fill in the details below</p>
            </div>
            <button type="button" data-modal-close="my-meeting" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="overflow-y-auto px-6 py-4 space-y-4 flex-1" style="scrollbar-width:thin;scrollbar-color:#d1d5db transparent">
            <form id="createMeeting" class="ajax-form space-y-4" method="POST">
                @csrf

                <div class="grid grid-cols-5 gap-3">
                    <div class="col-span-3">
                        <label class="block mb-1.5 text-xs font-semibold text-gray-600">Meeting Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="meeting_title" id="meeting_title" placeholder="Enter meeting title" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-gray-600 m-0">Category</label>
                            <button type="button" id="addCategory" class="w-5 h-5 rounded bg-blue-600 text-white flex items-center justify-center text-xs font-bold hover:bg-blue-700 transition-colors" title="Add category">+</button>
                        </div>
                        <select class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" id="category_id" name="category_id">
                            <option value="">Please Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ ucwords($category->category_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="description" id="description" rows="3" placeholder="Add meeting agenda or notes..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 resize-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-md p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Date &amp; Time</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Starts On <span class="text-rose-500">*</span></label>
                            <input type="text" name="start_date" id="start_date" class="new_date w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Start Time</label>
                            <input type="text" name="start_time" id="start_time" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Ends On <span class="text-rose-500">*</span></label>
                            <input type="text" name="end_date" id="end_date" autocomplete="none" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">End Time <span class="text-rose-500">*</span></label>
                            <input type="text" name="end_time" id="end_time" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Add Employees</label>
                    <select class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" multiple="multiple" id="employee_id" name="employee_id[]">
                        @foreach($attendees as $emp)
                            <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $currentUser->id) (YOU) @endif</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Host Video Status</p>
                        <div class="space-y-1.5">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="host_video" id="host_video1" value="1"> Enable</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="host_video" id="host_video2" value="0" checked> Disable</label>
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Participant Video Status</p>
                        <div class="space-y-1.5">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="participant_video" id="participant_video1" value="1"> Enable</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="participant_video" id="participant_video2" value="0" checked> Disable</label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Meeting Host</label>
                    <select class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" id="created_by" name="created_by">
                        @foreach($attendees as $emp)
                            <option @if($emp->id == $currentUser->id) selected @endif value="{{ $emp->id }}">{{ ucwords($emp->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" value="1" name="repeat" id="repeat-meeting" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Repeat</span>
                        </label>
                        <div class="grid grid-cols-1 gap-2 mt-2" id="repeat-fields" style="display: none">
                            <input type="number" min="1" value="1" name="repeat_every" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                            <select name="repeat_type" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="week">@lang('modules.zoommeeting.week')</option>
                                <option value="month">@lang('modules.zoommeeting.month')</option>
                            </select>
                            <input type="text" name="repeat_cycles" id="repeat_cycles" class="h-10 rounded-xl border border-gray-300 px-3 text-sm" placeholder="@lang('modules.zoommeeting.cycles')">
                        </div>
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="send_reminder" value="0">
                            <input type="checkbox" value="1" name="send_reminder" id="send_reminder" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Send Reminder</span>
                        </label>
                        <div class="grid grid-cols-1 gap-2 mt-2" id="reminder-fields" style="display: none;">
                            <input type="number" min="1" value="1" name="remind_time" class="h-10 rounded-xl border border-gray-300 px-3 text-sm" placeholder="@lang('modules.zoommeeting.remindBefore')">
                            <select name="remind_type" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="hour">@lang('modules.zoommeeting.hour')</option>
                                <option value="minute">@lang('modules.zoommeeting.minute')</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-2 bg-gray-50">
            <button type="button" data-modal-close="my-meeting" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Close</button>
            <button type="button" class="save-meeting inline-flex items-center rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Submit</button>
        </div>
    </div>
</div>
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" id="my-meeting" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl" id="modal-data-application">
        <div class="modal-header flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Schedule Meeting</h2>
                <p class="text-xs text-gray-400 mt-0.5">Fill in the details below</p>
            </div>
            <button type="button" data-modal-close="my-meeting" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="overflow-y-auto px-6 py-4 space-y-4 flex-1" style="scrollbar-width:thin;scrollbar-color:#d1d5db transparent">
            <form id="createMeeting" class="ajax-form space-y-4" method="POST">
                @csrf

                <div class="grid grid-cols-5 gap-3">
                    <div class="col-span-3">
                        <label class="block mb-1.5 text-xs font-semibold text-gray-600">Meeting Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="meeting_title" id="meeting_title" placeholder="Enter meeting title" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-gray-600 m-0">Category</label>
                            <button type="button" id="addCategory" class="w-5 h-5 rounded bg-blue-600 text-white flex items-center justify-center text-xs font-bold hover:bg-blue-700 transition-colors" title="Add category">+</button>
                        </div>
                        <select class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" id="category_id" name="category_id">
                            <option value="">Please Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ ucwords($category->category_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="description" id="description" rows="3" placeholder="Add meeting agenda or notes..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 resize-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-md p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Date &amp; Time</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Starts On <span class="text-rose-500">*</span></label>
                            <input type="text" name="start_date" id="start_date" class="new_date w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Start Time</label>
                            <input type="text" name="start_time" id="start_time" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Ends On <span class="text-rose-500">*</span></label>
                            <input type="text" name="end_date" id="end_date" autocomplete="none" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">End Time <span class="text-rose-500">*</span></label>
                            <input type="text" name="end_time" id="end_time" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Add Employees</label>
                    <select class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" multiple="multiple" id="employee_id" name="employee_id[]">
                        @foreach($attendees as $emp)
                            <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $currentUser->id) (YOU) @endif</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Host Video Status</p>
                        <div class="space-y-1.5">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="host_video" id="host_video1" value="1"> Enable</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="host_video" id="host_video2" value="0" checked> Disable</label>
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Participant Video Status</p>
                        <div class="space-y-1.5">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="participant_video" id="participant_video1" value="1"> Enable</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="participant_video" id="participant_video2" value="0" checked> Disable</label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Meeting Host</label>
                    <select class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" id="created_by" name="created_by">
                        @foreach($attendees as $emp)
                            <option @if($emp->id == $currentUser->id) selected @endif value="{{ $emp->id }}">{{ ucwords($emp->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" value="1" name="repeat" id="repeat-meeting" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Repeat</span>
                        </label>
                        <div class="grid grid-cols-1 gap-2 mt-2" id="repeat-fields" style="display: none">
                            <input type="number" min="1" value="1" name="repeat_every" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                            <select name="repeat_type" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="week">@lang('modules.zoommeeting.week')</option>
                                <option value="month">@lang('modules.zoommeeting.month')</option>
                            </select>
                            <input type="text" name="repeat_cycles" id="repeat_cycles" class="h-10 rounded-xl border border-gray-300 px-3 text-sm" placeholder="@lang('modules.zoommeeting.cycles')">
                        </div>
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="send_reminder" value="0">
                            <input type="checkbox" value="1" name="send_reminder" id="send_reminder" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Send Reminder</span>
                        </label>
                        <div class="grid grid-cols-1 gap-2 mt-2" id="reminder-fields" style="display: none;">
                            <input type="number" min="1" value="1" name="remind_time" class="h-10 rounded-xl border border-gray-300 px-3 text-sm" placeholder="@lang('modules.zoommeeting.remindBefore')">
                            <select name="remind_type" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="hour">@lang('modules.zoommeeting.hour')</option>
                                <option value="minute">@lang('modules.zoommeeting.minute')</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-2 bg-gray-50">
            <button type="button" data-modal-close="my-meeting" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Close</button>
            <button type="button" class="save-meeting inline-flex items-center rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Submit</button>
        </div>
    </div>
</div>
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" id="my-meeting" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl" id="modal-data-application">
        <div class="modal-header flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Schedule Meeting</h2>
                <p class="text-xs text-gray-400 mt-0.5">Fill in the details below</p>
            </div>
            <button type="button" data-modal-close="my-meeting" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="overflow-y-auto px-6 py-4 space-y-4 flex-1" style="scrollbar-width:thin;scrollbar-color:#d1d5db transparent">
            <form id="createMeeting" class="ajax-form space-y-4" method="POST">
                @csrf

                <div class="grid grid-cols-5 gap-3">
                    <div class="col-span-3">
                        <label class="block mb-1.5 text-xs font-semibold text-gray-600">Meeting Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="meeting_title" id="meeting_title" placeholder="Enter meeting title" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-gray-600 m-0">Category</label>
                            <button type="button" id="addCategory" class="w-5 h-5 rounded bg-blue-600 text-white flex items-center justify-center text-xs font-bold hover:bg-blue-700 transition-colors" title="Add category">+</button>
                        </div>
                        <select class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" id="category_id" name="category_id">
                            <option value="">Please Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ ucwords($category->category_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="description" id="description" rows="3" placeholder="Add meeting agenda or notes..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 resize-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-md p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Date &amp; Time</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Starts On <span class="text-rose-500">*</span></label>
                            <input type="text" name="start_date" id="start_date" class="new_date w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Start Time</label>
                            <input type="text" name="start_time" id="start_time" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Ends On <span class="text-rose-500">*</span></label>
                            <input type="text" name="end_date" id="end_date" autocomplete="none" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">End Time <span class="text-rose-500">*</span></label>
                            <input type="text" name="end_time" id="end_time" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Add Employees</label>
                    <select class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" multiple="multiple" id="employee_id" name="employee_id[]">
                        @foreach($attendees as $emp)
                            <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $currentUser->id) (YOU) @endif</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Host Video Status</p>
                        <div class="space-y-1.5">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="host_video" id="host_video1" value="1"> Enable</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="host_video" id="host_video2" value="0" checked> Disable</label>
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Participant Video Status</p>
                        <div class="space-y-1.5">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="participant_video" id="participant_video1" value="1"> Enable</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="participant_video" id="participant_video2" value="0" checked> Disable</label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Meeting Host</label>
                    <select class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" id="created_by" name="created_by">
                        @foreach($attendees as $emp)
                            <option @if($emp->id == $currentUser->id) selected @endif value="{{ $emp->id }}">{{ ucwords($emp->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" value="1" name="repeat" id="repeat-meeting" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Repeat</span>
                        </label>
                        <div class="grid grid-cols-1 gap-2 mt-2" id="repeat-fields" style="display: none">
                            <input type="number" min="1" value="1" name="repeat_every" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                            <select name="repeat_type" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="week">@lang('modules.zoommeeting.week')</option>
                                <option value="month">@lang('modules.zoommeeting.month')</option>
                            </select>
                            <input type="text" name="repeat_cycles" id="repeat_cycles" class="h-10 rounded-xl border border-gray-300 px-3 text-sm" placeholder="@lang('modules.zoommeeting.cycles')">
                        </div>
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="send_reminder" value="0">
                            <input type="checkbox" value="1" name="send_reminder" id="send_reminder" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Send Reminder</span>
                        </label>
                        <div class="grid grid-cols-1 gap-2 mt-2" id="reminder-fields" style="display: none;">
                            <input type="number" min="1" value="1" name="remind_time" class="h-10 rounded-xl border border-gray-300 px-3 text-sm" placeholder="@lang('modules.zoommeeting.remindBefore')">
                            <select name="remind_type" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="hour">@lang('modules.zoommeeting.hour')</option>
                                <option value="minute">@lang('modules.zoommeeting.minute')</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-2 bg-gray-50">
            <button type="button" data-modal-close="my-meeting" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Close</button>
            <button type="button" class="save-meeting inline-flex items-center rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Submit</button>
        </div>
    </div>
</div>
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" id="my-meeting" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl" id="modal-data-application">
        <div class="modal-header flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Schedule Meeting</h2>
                <p class="text-xs text-gray-400 mt-0.5">Fill in the details below</p>
            </div>
            <button type="button" data-modal-close="my-meeting" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="overflow-y-auto px-6 py-4 space-y-4 flex-1" style="scrollbar-width:thin;scrollbar-color:#d1d5db transparent">
            <form id="createMeeting" class="ajax-form space-y-4" method="POST">
                @csrf

                <div class="grid grid-cols-5 gap-3">
                    <div class="col-span-3">
                        <label class="block mb-1.5 text-xs font-semibold text-gray-600">Meeting Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="meeting_title" id="meeting_title" placeholder="Enter meeting title" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-gray-600 m-0">Category</label>
                            <button type="button" id="addCategory" class="w-5 h-5 rounded bg-blue-600 text-white flex items-center justify-center text-xs font-bold hover:bg-blue-700 transition-colors" title="Add category">+</button>
                        </div>
                        <select class="select2 w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" id="category_id" name="category_id">
                            <option value="">Please Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ ucwords($category->category_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="description" id="description" rows="3" placeholder="Add meeting agenda or notes..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 resize-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-md p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Date &amp; Time</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Starts On <span class="text-rose-500">*</span></label>
                            <input type="text" name="start_date" id="start_date" class="new_date w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Start Time</label>
                            <input type="text" name="start_time" id="start_time" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">Ends On <span class="text-rose-500">*</span></label>
                            <input type="text" name="end_date" id="end_date" autocomplete="none" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">End Time <span class="text-rose-500">*</span></label>
                            <input type="text" name="end_time" id="end_time" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Add Employees</label>
                    <select class="select2 select2-multiple w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" multiple="multiple" id="employee_id" name="employee_id[]">
                        @foreach($attendees as $emp)
                            <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $currentUser->id) (YOU) @endif</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Host Video Status</p>
                        <div class="space-y-1.5">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="host_video" id="host_video1" value="1"> Enable</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="host_video" id="host_video2" value="0" checked> Disable</label>
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">Participant Video Status</p>
                        <div class="space-y-1.5">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="participant_video" id="participant_video1" value="1"> Enable</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="radio" name="participant_video" id="participant_video2" value="0" checked> Disable</label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">Meeting Host</label>
                    <select class="select2 w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" id="created_by" name="created_by">
                        @foreach($attendees as $emp)
                            <option @if($emp->id == $currentUser->id) selected @endif value="{{ $emp->id }}">{{ ucwords($emp->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" value="1" name="repeat" id="repeat-meeting" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Repeat</span>
                        </label>
                        <div class="grid grid-cols-1 gap-2 mt-2" id="repeat-fields" style="display: none">
                            <input type="number" min="1" value="1" name="repeat_every" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                            <select name="repeat_type" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="week">@lang('modules.zoommeeting.week')</option>
                                <option value="month">@lang('modules.zoommeeting.month')</option>
                            </select>
                            <input type="text" name="repeat_cycles" id="repeat_cycles" class="h-10 rounded-xl border border-gray-300 px-3 text-sm" placeholder="@lang('modules.zoommeeting.cycles')">
                        </div>
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="send_reminder" value="0">
                            <input type="checkbox" value="1" name="send_reminder" id="send_reminder" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Send Reminder</span>
                        </label>
                        <div class="grid grid-cols-1 gap-2 mt-2" id="reminder-fields" style="display: none;">
                            <input type="number" min="1" value="1" name="remind_time" class="h-10 rounded-xl border border-gray-300 px-3 text-sm" placeholder="@lang('modules.zoommeeting.remindBefore')">
                            <select name="remind_type" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="hour">@lang('modules.zoommeeting.hour')</option>
                                <option value="minute">@lang('modules.zoommeeting.minute')</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-2 bg-gray-50">
            <button type="button" data-modal-close="my-meeting" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Close</button>
            <button type="button" class="save-meeting inline-flex items-center rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Submit</button>
        </div>
    </div>
</div>
<div class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-slate-900/45 p-4" id="my-meeting" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-bg w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl" id="modal-data-application">
        <div class="modal-header flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h2 class="text-base font-semibold text-gray-800">@lang('modules.zoommeeting.addMeeting')</h2>
                <p class="text-xs text-gray-400 mt-0.5">@lang('modules.zoommeeting.meetingName')</p>
            </div>
            <button type="button" data-modal-close="my-meeting" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="overflow-y-auto px-6 py-4 space-y-4 flex-1">
            <form id="createMeeting" class="ajax-form space-y-4" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div class="md:col-span-3">
                        <label class="block mb-1.5 text-xs font-semibold text-gray-600">@lang('modules.zoommeeting.meetingName') <span class="text-rose-500">*</span></label>
                        <input type="text" name="meeting_title" id="meeting_title" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="@lang('modules.zoommeeting.meetingName')">
                    </div>
                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-gray-600">@lang('app.category')</label>
                            <button type="button" id="addCategory" class="w-5 h-5 rounded bg-blue-600 text-white flex items-center justify-center text-xs font-bold hover:bg-blue-700 transition-colors">+</button>
                        </div>
                        <select class="select2 form-control w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" id="category_id" name="category_id" style="width: 100%;">
                            <option value="">@lang('modules.message.pleaseSelectCategory')</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ ucwords($category->category_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">@lang('modules.zoommeeting.description') <span class="text-gray-400 font-normal">(@lang('app.optional'))</span></label>
                    <textarea name="description" id="description" rows="3" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 resize-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="@lang('modules.zoommeeting.description')"></textarea>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-md p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">@lang('modules.zoommeeting.startOn') / @lang('modules.zoommeeting.endOn')</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">@lang('modules.zoommeeting.startOn') <span class="text-rose-500">*</span></label>
                            <input type="text" name="start_date" id="start_date" class="new_date w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">@lang('modules.zoommeeting.startTime')</label>
                            <input type="text" name="start_time" id="start_time" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">@lang('modules.zoommeeting.endOn') <span class="text-rose-500">*</span></label>
                            <input type="text" name="end_date" id="end_date" autocomplete="none" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-xs font-semibold text-gray-600">@lang('modules.zoommeeting.endTime') <span class="text-rose-500">*</span></label>
                            <input type="text" name="end_time" id="end_time" class="w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">@lang('modules.meetings.addEmployees')</label>
                    <select class="select2 form-control select2-multiple w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" multiple="multiple" id="employee_id" name="employee_id[]">
                        @foreach($attendees as $emp)
                            <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $currentUser->id) (YOU) @endif</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">@lang('modules.zoommeeting.hostVideoStatus')</p>
                        <div class="space-y-1.5 text-sm text-gray-700">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="host_video" id="host_video1" value="1" class="text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span>@lang('app.enable')</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="host_video" id="host_video2" value="0" checked class="text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span>@lang('app.disable')</span>
                            </label>
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-3">
                        <p class="text-xs font-semibold text-gray-600 mb-2">@lang('modules.zoommeeting.participantVideoStatus')</p>
                        <div class="space-y-1.5 text-sm text-gray-700">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="participant_video" id="participant_video1" value="1" class="text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span>@lang('app.enable')</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="participant_video" id="participant_video2" value="0" checked class="text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span>@lang('app.disable')</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-xs font-semibold text-gray-600">@lang('modules.zoommeeting.meetingHost')</label>
                    <select class="select2 form-control w-full h-10 rounded-xl border border-gray-300 px-3 text-sm text-gray-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100" id="created_by" name="created_by">
                        @foreach($attendees as $emp)
                            <option @if($emp->id == $currentUser->id) selected @endif value="{{ $emp->id }}">{{ ucwords($emp->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" value="1" name="repeat" id="repeat-meeting" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>@lang('modules.zoommeeting.repeat')</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2" id="repeat-fields" style="display: none">
                            <input type="number" min="1" value="1" name="repeat_every" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                            <select name="repeat_type" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="week">@lang('modules.zoommeeting.week')</option>
                                <option value="month">@lang('modules.zoommeeting.month')</option>
                            </select>
                            <input type="text" name="repeat_cycles" id="repeat_cycles" class="h-10 rounded-xl border border-gray-300 px-3 text-sm" placeholder="@lang('modules.zoommeeting.cycles')">
                        </div>
                    </div>
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="send_reminder" value="0">
                            <input type="checkbox" value="1" name="send_reminder" id="send_reminder" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>@lang('modules.zoommeeting.reminder')</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2" id="reminder-fields" style="display: none;">
                            <input type="number" min="1" value="1" name="remind_time" class="h-10 rounded-xl border border-gray-300 px-3 text-sm" placeholder="@lang('modules.zoommeeting.remindBefore')">
                            <select name="remind_type" class="h-10 rounded-xl border border-gray-300 px-3 text-sm">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="hour">@lang('modules.zoommeeting.hour')</option>
                                <option value="minute">@lang('modules.zoommeeting.minute')</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-2 bg-gray-50">
            <button type="button" data-modal-close="my-meeting" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">@lang('app.close')</button>
            <button type="button" class="save-meeting inline-flex items-center rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">@lang('app.submit')</button>
        </div>
    </div>
</div>
<div class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-slate-900/45 p-4" id="my-meeting" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="w-full max-w-6xl rounded-2xl border border-slate-200 bg-white shadow-2xl" id="modal-data-application">
   <div>
    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4">
        <h4 class="modal-title"><i class="icon-plus"></i> @lang('modules.zoommeeting.addMeeting')</h4>
        <button type="button" class="text-slate-400 transition hover:text-slate-700" data-modal-close="my-meeting" aria-hidden="true"><i class="fa fa-times"></i></button>
        </div>
       <div class="px-6 py-5">
        <form id="createMeeting" class="ajax-form" method="POST">
            @csrf
            <div class="form-body space-y-4">
                <div class="row grid grid-cols-1 gap-4 md:grid-cols-12">
                    <div class="col-md-6 md:col-span-7">
                        <div class="form-group mb-0">
                            <label class="d-block mb-1 text-sm font-semibold text-slate-700">@lang('modules.zoommeeting.meetingName')</label>
                            <input type="text" name="meeting_title" id="meeting_title" class="form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        </div>
                    </div>
                    <div class="col-md-4 md:col-span-5">
                        <div class="form-group mb-0">
                            <label class="control-label mb-1 inline-flex items-center gap-2 text-sm font-semibold text-slate-700">@lang('app.category')
                                <a href="javascript:;" id="addCategory" class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-emerald-600 text-white transition hover:bg-emerald-700"><i class="fa fa-plus text-[10px]"></i></a>
                                 </label>
                                 <select class="select2 form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" id="category_id" name="category_id" style="width: 100%;">
                                    <option value="">@lang('modules.message.pleaseSelectCategory')</option>
                                     @foreach($categories as $category)
                                         <option value="{{ $category->id }}">{{ ucwords($category->category_name) }}</option>
                                     @endforeach
                                    </select>
                        </div>
                    </div>
                </div>

                <div class="row grid grid-cols-1 gap-4 md:grid-cols-12">
                    <div class="col-xs-12 col-md-12 md:col-span-12">
                        <div class="form-group mb-0">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">@lang('modules.zoommeeting.description')</label>
                            <textarea type="text" name="description" id="description" class="form-control rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]"></textarea>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-3 md:col-span-3">
                        <div class="form-group chooseCandidate mb-0">
                            <label class="required mb-1 block text-sm font-semibold text-slate-700">@lang('modules.zoommeeting.startOn')</label>
                            <input type="text" name="start_date" id="start_date" class="form-control new_date h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" >
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-3 md:col-span-3">
                        <div class="form-group bootstrap-timepicker timepicker mb-0">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">@lang('modules.zoommeeting.startTime')</label>
                            <input type="text" name="start_time" id="start_time" class="form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-3 md:col-span-3">
                        <div class="form-group chooseCandidate bootstrap-timepicker timepicker mb-0">
                            <label class="required mb-1 block text-sm font-semibold text-slate-700">@lang('modules.zoommeeting.endOn')</label>
                            <input type="text" name="end_date" id="end_date" class="form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" autocomplete="none">
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-3 md:col-span-3">
                        <div class="form-group bootstrap-timepicker timepicker mb-0">
                            <label class="required mb-1 block text-sm font-semibold text-slate-700">@lang('modules.zoommeeting.endTime')</label>
                            <input type="text" name="end_time" id="end_time" class="form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-12">
                        <div class="form-group mb-0">
                            <label class="control-label mb-1 block text-sm font-semibold text-slate-700">@lang('modules.meetings.addEmployees')</label>
                            <select class="select2 form-control select2-multiple rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" multiple="multiple" id="employee_id" name="employee_id[]">
                                @foreach($attendees as $emp)
                                    <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $currentUser->id) (YOU) @endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-4">
                        <div class="form-group mb-0 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="m-b-10">
                                <label class="control-label">@lang('modules.zoommeeting.hostVideoStatus')</label>
                            </div>
                            <div class="radio radio-inline mr-4">
                                <input type="radio" name="host_video" id="host_video1" value="1">
                                <label for="host_video1" class=""> @lang('app.enable') </label>
                            </div>
                            <div class="radio radio-inline ">
                                <input type="radio" name="host_video" id="host_video2" value="0" checked>
                                <label for="host_video2" class=""> @lang('app.disable') </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="m-b-10">
                                <label class="control-label">@lang('modules.zoommeeting.participantVideoStatus')</label>
                            </div>
                            <div class="radio radio-inline mr-4">
                                <input type="radio" name="participant_video" id="participant_video1" value="1">
                                <label for="participant_video1" class=""> @lang('app.enable') </label>
                            </div>
                            <div class="radio radio-inline ">
                                <input type="radio" name="participant_video" id="participant_video2" value="0" checked>
                                <label for="participant_video2" class=""> @lang('app.disable') </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                              <label class="control-label mb-1 block text-sm font-semibold text-slate-700">@lang('modules.zoommeeting.meetingHost')</label>
                              <select class="select2 form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" id="created_by" name="created_by">
                                  @foreach($attendees as $emp)
                                      <option @if($emp->id == $currentUser->id) selected @endif value="{{ $emp->id }}">{{ ucwords($emp->name) }}</option>
                                  @endforeach
                              </select>
                        </div>
                    </div>
                </div>
                <div class="row pt-1">
                    <div class="form-group">
                        <div class="col-xs-6">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input type="checkbox" value="1" name="repeat" id ="repeat-meeting" class="h-4 w-4 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB]">
                                @lang('modules.zoommeeting.repeat')
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row" id="repeat-fields" style="display: none">
                    <div class="col-xs-6 col-md-3 ">
                        <div class="form-group mb-0">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">@lang('modules.zoommeeting.repeatEvery')</label>
                            <input type="number" min="1" value="1" name="repeat_every" class="form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        </div>
                    </div>
                    <div class="col-xs-6 col-md-3">
                        <div class="form-group repeat_type_dropdown mb-0">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">&nbsp;</label>
                            <select name="repeat_type" id="" class="form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="week">@lang('modules.zoommeeting.week')</option>
                                <option value="month">@lang('modules.zoommeeting.month')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-6 col-md-3">
                        <div class="form-group mb-0">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">@lang('modules.zoommeeting.cycles') <a class="mytooltip" href="javascript:void(0)"> </a></label>
                            <input type="text" name="repeat_cycles" id="repeat_cycles" class="form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <div class="col-xs-6">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input type="hidden" name="send_reminder" value="0">
                                <input type="checkbox" value="1" name="send_reminder" id ="send_reminder" class="h-4 w-4 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB]">
                                @lang('modules.zoommeeting.reminder')
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row" id="reminder-fields" style="display: none;">
                    <div class="col-xs-6 col-md-3">
                        <div class="form-group mb-0">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">@lang('modules.zoommeeting.remindBefore')</label>
                            <input type="number" min="1" value="1" name="remind_time" class="form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        </div>
                    </div>
                    <div class="col-xs-6 col-md-3">
                        <div class="form-group repeat_type_dropdown mb-0">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">&nbsp;</label>
                            <select name="remind_type" id="" class="form-control h-10 rounded-lg border-slate-300 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                                <option value="day">@lang('modules.zoommeeting.day')</option>
                                <option value="hour">@lang('modules.zoommeeting.hour')</option>
                                <option value="minute">@lang('modules.zoommeeting.minute')</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
        <button type="button" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-modal-close="my-meeting">@lang('app.close')</button>
        <button type="button" class="save-meeting inline-flex items-center justify-center rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]">@lang('app.submit')</button>
    </div>
   </div>
</div>
</div>
