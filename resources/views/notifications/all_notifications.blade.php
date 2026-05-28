<div class="bg-white rounded-lg shadow-md border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between"><i class="icon-note"></i> <span>{{ count($user->unreadNotifications) }} Unread Notifications</span>
        <div class="flex items-center">
            <a href="javascript:;" class="close text-gray-400 hover:text-gray-600 transition-colors" data-dismiss="modal"><i class="ti-close"></i></a>
        </div>
    </div>
    <div class="p-6">
        <div class="w-full">
            @foreach ($user->unreadNotifications as $notification)
                @include('notifications.detail_'.snake_case(class_basename($notification->type)))
            @endforeach
        </div>

    </div>
</div>
