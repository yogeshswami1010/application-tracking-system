<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OnboardAnswer extends Model
{
    protected $appends = ['file_url'];

    public function question(){
        return $this->belongsTo(JobOfferQuestion::class, 'question_id');
    }

    public function getFileUrlAttribute()
    {
        if (is_null($this->file)) {
            return asset('avatar.png');
        }
        
        return asset_url_local_s3('documents/' . $this->file);
    }
}
