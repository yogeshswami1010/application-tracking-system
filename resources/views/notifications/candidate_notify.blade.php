<a href="javascript:;" data-link ="{{ route('admin.job-applications.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-100 read-notification" data-notification-id="{{ $notification->id }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center flex-1 min-w-0">
            <i class="fa fa-users mr-2"></i>
            <span class="text-truncate-notify truncate" style="overflow-y: hidden" title="full name">@lang('messages.notifications.candidateNotify')</span>
        </div>
        <span class="text-gray-500 text-sm ml-2 flex-shrink-0">{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $notification->created_at)->diffForHumans() }}</span>
    </div>
</a>