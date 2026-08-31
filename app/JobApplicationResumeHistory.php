<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobApplicationResumeHistory extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['resume_url'];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getResumeUrlAttribute()
    {
        return asset_url_local_s3('documents/'.$this->job_application_id.'/'.$this->hashname);
    }
}
