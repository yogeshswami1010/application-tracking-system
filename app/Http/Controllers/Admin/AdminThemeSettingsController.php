<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\ThemeSetting;
use Illuminate\Http\Request;
use App\Helper\Files;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Theme;

class AdminThemeSettingsController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.themeSettings');
        $this->pageIcon = 'ti-settings';
    }

    public function index(){
        $this->disable = ThemeSetting::get();
        return view('admin.theme-settings.index', $this->data);
    }

    public function store(Request $request){
        $theme = ThemeSetting::first();

        $theme->primary_color    = $request->primary_color;
        $theme->front_custom_css =  $request->front_custom_css;
        $theme->admin_custom_css =  $request->admin_custom_css;
        $theme->welcome_title =  $request->welcome_title;
        $theme->welcome_sub_title =  $request->welcome_sub_title;

        $oldImage = $theme->home_background_image;

        if ($request->hasFile('image')) {
            $theme->home_background_image = Files::uploadLocalOrS3($request->image, 'background');
            Files::deleteFile($oldImage, 'background');
        }

        $theme->save();

        return Reply::redirect(route('admin.theme-settings.index'), __('menu.themeSettings').' '.__('messages.updatedSuccessfully'));
    }

    public function disableFrontend(Request $request)
    {
        $setting = ThemeSetting::first();
        $setting->disable_frontend = $request->disable_frontend;
        $setting->save();
    }

}
