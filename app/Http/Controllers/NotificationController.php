<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\Admin\AdminBaseController;
use Illuminate\Http\Request;

class NotificationController extends AdminBaseController
{
    
    public function markAllRead() {
        $this->user->unreadNotifications->markAsRead();
        return Reply::success(__('messages.notificationRead'));
    }

    public function markRead(Request $request)
    {
        $this->user->unreadNotifications->where('id', $request->id)->markAsRead();
        return Reply::dataOnly(['status' => 'success']);
    }

}
