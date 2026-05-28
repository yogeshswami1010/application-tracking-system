<?php

namespace App\Http\Controllers\Admin;

use App\EmailSetting;
use App\Helper\Reply;
use App\Http\Requests\SmtpSetting\UpdateSmtpSetting;
use App\Notifications\TestEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AdminSmtpSettingController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.mailSetting');
        $this->pageIcon = 'ti-user';
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->smtpSetting = EmailSetting::first();

        return view('admin.mail-setting.index', $this->data);
    }

    /**
     * @return array
     */
    public function update(UpdateSmtpSetting $request)
    {
        $smtp = EmailSetting::first();

        $data = $request->all();

        if ($request->mail_encryption == 'null') {
            $data['mail_encryption'] = null;
        }

        $smtp->update($data);
        $smtp->refresh();

        cache()->forget('smtp_setting');
        session()->forget('smtp_setting');

        $response = $smtp->verifySmtp();

        if ($smtp->mail_driver == 'mail') {
            return Reply::success(__('messages.updatedSuccessfully'));
        }

        if ($response['success']) {
            return Reply::success($response['message']);
        }

        if ($smtp->mail_host == 'smtp.gmail.com') {
            $message = __('messages.smtpError').'<br><br>';
            $secureUrl = 'https://froiden.freshdesk.com/support/solutions/articles/43000672983';
            $message .= __('messages.smtpSecureEnabled');
            $message .= '<a class="underline underline-offset-1 mb-2" target="_blank" href="'.$secureUrl.'">'.$secureUrl.'</a>';
            $message .= '<div class="mt-2">'.$response['message'].'</div>';

            return Reply::error($message);
        }

        $message = '<strong>'.__('messages.smtpError').'</strong><ul><li class="py-2">'.$response['message'].'</li></ul>';

        return Reply::error($message);
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $smtp = EmailSetting::first();
        $response = $smtp->verifySmtp();

        if (! $response['success']) {
            return Reply::error($response['message']);
        }

        try {
            Notification::route('mail', $request->input('test_email'))->notify(new TestEmail());

            return Reply::success('Test mail sent successfully');
        } catch (\Exception $e) {
            return Reply::error('Failed to send test email: '.$e->getMessage());
        }
    }
}
