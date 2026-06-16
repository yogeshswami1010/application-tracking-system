<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobApplicationStatusHistory extends Model
{
    protected $fillable = ['job_application_id', 'from_status_id', 'to_status_id', 'user_id'];

    public function fromStatus()
    {
        return $this->belongsTo(ApplicationStatus::class, 'from_status_id');
    }

    public function toStatus()
    {
        return $this->belongsTo(ApplicationStatus::class, 'to_status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}