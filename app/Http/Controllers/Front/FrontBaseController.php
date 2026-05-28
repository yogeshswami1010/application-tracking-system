<?php

namespace App\Http\Controllers\Front;

use Carbon\Carbon;
use App\ThemeSetting;
use App\FooterSetting;
use App\CompanySetting;
use App\LanguageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Job;
use App\JobAlert;
use App\Traits\FileSystemSettingTrait;
use Illuminate\Support\Facades\Cookie;

class FrontBaseController extends Controller
{
    use FileSystemSettingTrait;
    /**
     * @var array
     */
    public $data = [];

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[ $name ]);
    }

    /**
     * UserBaseController constructor.
     */
    public function __construct()
    {
        // Inject currently logged in user object into every view of user dashboard
        parent::__construct();
        $hash = request()->hash;
        $this->global = CompanySetting::first();
//        $this->emailSetting = EmailNotificationSetting::all();
        $this->companyName = $this->global->company_name;
        if(!is_null($hash)){
            
            $this->alertId = JobAlert::where('hash', $hash)->first()->id;
        }
        $this->frontTheme = ThemeSetting::first();
        $this->customPages = FooterSetting::where('status', 'active')->get();
        $this->languageSettings = LanguageSetting::where('status', 'enabled')->get();
        $this->setFileSystemConfigs();
        if (Cookie::get('language_code')) {
           
            $langArray = explode('|', decrypt(Cookie::get('language_code'), false));
            if(isset($langArray[1]))
            {
             $this->global->locale = $langArray[1];
             App::setLocale($this->global->locale);
            }else{
             $this->global->locale = Cookie::get('language_code');
             App::setLocale($this->global->locale);
            }
         } else {
             App::setLocale($this->global->locale);
         }
         $this->language = LanguageSetting::where('language_code',  $this->global->locale)->first();
        
        setlocale(LC_TIME,$this->global->locale.'_'.strtoupper($this->global->locale));
        Carbon::setLocale($this->global->locale);
    }
}
