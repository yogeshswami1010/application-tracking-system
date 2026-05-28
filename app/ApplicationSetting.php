<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicationSetting extends Model
{
    protected $casts = [
        'mail_setting' => 'array'
    ];
}
