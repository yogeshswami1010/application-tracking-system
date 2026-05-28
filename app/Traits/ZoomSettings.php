<?php
namespace App\Traits;

use App\ZoomSetting;
use Illuminate\Support\Facades\Config;

trait ZoomSettings{
    public function setZoomConfigs(){
        $settings = ZoomSetting::first();
        $key       = ($settings->api_key)? $settings->api_key : env('STRIPE_KEY');
        $apiSecret = ($settings->secret_key)? $settings->secret_key : env('STRIPE_SECRET');
        $accountId = ($settings->account_id) ?: env('ZOOM_ACCOUNT_ID');
        Config::set('zoom.api_key', $key);
        Config::set('zoom.api_secret', $apiSecret);
        Config::set('zoom.account_id', $accountId);
    }
}
