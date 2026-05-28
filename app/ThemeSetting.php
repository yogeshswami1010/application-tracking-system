<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    //

    public function getBackgroundImageUrlAttribute(){
        if(is_null($this->home_background_image)){
            return asset('front/assets/img/header-bg.jpg');
        }
        return asset_url_local_s3('background/'.$this->home_background_image);
    }
}
