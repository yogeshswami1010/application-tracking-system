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
        if ($request->has('email_signature')) {
            $user->email_signature = trim((string) $request->input('email_signature')) ?: null;
        }
        if ($request->has('email_signature_html')) {
            $user->email_signature_html = \App\Services\EmailSignatureHtml::clean($request->input('email_signature_html')) ?: null;
        }
        $oldSignatureImage = $user->email_signature_image;
        if ($request->hasFile('email_signature_image')) {
            $user->email_signature_image = Files::uploadLocalOrS3($request->file('email_signature_image'), 'email-signatures');
        } elseif ($request->boolean('remove_email_signature_image')) {
            $user->email_signature_image = null;
        }
        $user->save();
        if ($oldSignatureImage && $oldSignatureImage !== $user->email_signature_image) {
            Files::deleteFile($oldSignatureImage, 'email-signatures');
        }

        return Reply::redirect(route('admin.profile.index'), __('menu.myProfile') . ' ' . __('messages.updatedSuccessfully'));
    }
}
