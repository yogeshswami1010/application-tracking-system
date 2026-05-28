<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\GoogleCaptchaSetting;
use Froiden\Envato\Helpers\Reply;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminSecurityController extends AdminBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function __construct() 
     {
         parent::__construct();
         $this->pageTitle = __('app.googleRecaptchaCredential');
         $this->pageIcon = 'icon-settings';
     }     

    public function index()
    {
        $this->key = request()->key;
        $this->credentials = GoogleCaptchaSetting::first();
        return view('admin.security-setting.index', $this->data);
    }

    public function verifyCaptcha()
    {
        // dd("hello");
        $this->key = request()->key;
        return view('admin.security-setting.verify-recaptcha-v3', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $google_captcha_setting = GoogleCaptchaSetting::first();
        if($request->version == 'v3') {
            $google_captcha_setting->v3_site_key = $request->v3_site_key;
                $google_captcha_setting->v3_secret_key = $request->v3_secret_key;
                $google_captcha_setting->v3_status = 'active';
                $google_captcha_setting->v2_status = 'inactive';
        }
        else {
            $google_captcha_setting->v2_site_key = $request->v2_site_key;
                $google_captcha_setting->v2_secret_key = $request->v2_secret_key;
                $google_captcha_setting->v2_status = 'active';
                $google_captcha_setting->v3_status = 'inactive';
        }

        if($request->status == 'inactive') {
            $google_captcha_setting->v2_status = 'inactive';
            $google_captcha_setting->v3_status = 'inactive';
            $google_captcha_setting->status = 'inactive';
        }
        else {
            $google_captcha_setting->status = 'active';
        }

        $google_captcha_setting->login_page = $request->login_page ? $request->login_page : 'inactive';
        $google_captcha_setting->job_apply_page = $request->job_apply_page ? $request->job_apply_page : 'inactive';
        $google_captcha_setting->save();

        return Reply::success( __('messages.updatedSuccessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
