<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobAlertLocation extends Model
{
    //

    public function location()
    {   
        return $this->belongsTo(JobLocation::class, 'location_id');
    }
}
