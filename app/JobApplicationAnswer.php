<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobApplicationAnswer extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['file_url'];

    public function job(){
        return $this->belongsTo(Job::class);
    }

    public function jobApplication(){
        return $this->belongsTo(JobApplication::class);
    }

    public function question(){
        return $this->belongsTo(Question::class);
    }

    public function getFileUrlAttribute()
    {
        if (is_null($this->file)) {
            return asset('avatar.png');
        }
        
        return asset_url_local_s3('documents/' . $this->file);
    }
}
