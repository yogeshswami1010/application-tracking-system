<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobApplicationTempStaffingHistory extends Model
{
    protected $guarded = ['id'];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}