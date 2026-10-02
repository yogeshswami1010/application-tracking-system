<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\UpdateProfile;
use App\SmsSetting;
use App\User;
use Illuminate\Support\Facades\Hash;

class AdminProfileController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.myProfile');
        $this->pageIcon = 'ti-user';
        $this->smsSettings = SmsSetting::first();
    }

    public function index()
    {
        $this->calling_codes = $this->getCallingCodes();
        return view('admin.profile.index', $this->data);
    }

    public function uploadSignatureImage(\Illuminate\Http\Request $request)
    {
        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png|max:2048|dimensions:max_width=4000,max_height=4000']);
        $name = Files::uploadLocalOrS3($request->file('image'), 'email-signatures');
        return response()->json(['url' => asset_url_local_s3('email-signatures/'.$name)]);
    }

    public function update(UpdateProfile $request)
    {
        $user = User::find($this->user->id);
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->password != '') {
            $user->password = Hash::make($request->password);
        }

        if ($request->has('mobile')) {
            if ($user->mobile !== $request->mobile || $user->calling_code !== $request->calling_code) {
                $user->mobile_verified = 0;
            }

            $user->mobile = $request->mobile;
            $user->calling_code = $request->calling_code;
        }

        if ($request->hasFile('image')) {
            Files::deleteFile($user->image, 'profile');
            $user->image = Files::uploadLocalOrS3($request->image, 'profile');
        }
        if ($request->exists('email_signature_payload')) {
            $html = base64_decode((string) $request->input('email_signature_payload'), true);
            if ($html === false || strlen($html) > 50000 || !mb_check_encoding($html, 'UTF-8')) {
                throw \Illuminate\Validation\ValidationException::withMessages(['email_signature_payload' => 'The signature could not be saved. Please use a signature under 50 KB.']);
            }
            $cleaned = \App\Services\EmailSignatureHtml::clean($html);
            if (trim($html) !== '' && trim(strip_tags($cleaned)) === '' && !str_contains($cleaned, '<img')) {
                throw \Illuminate\Validation\ValidationException::withMessages(['email_signature_payload' => 'The signature contains no supported text or images. Please paste formatted text and use Add images for local images.']);
            }
            $user->email_signature_html = $cleaned ?: null;
            // The single visual editor replaces legacy signature inputs, including when cleared.
            $user->email_signature = null;
            $user->email_signature_image = null;
        }
        $user->save();

        return Reply::redirect(route('admin.profile.index'), __('menu.myProfile') . ' ' . __('messages.updatedSuccessfully'));
    }
}
