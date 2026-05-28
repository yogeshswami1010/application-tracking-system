<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobAlertCategory extends Model
{
    //

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }
}
