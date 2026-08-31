<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsortiumRegistrationJobMove extends Model
{
    protected $guarded = ['id'];

    public function registration()
    {
        return $this->belongsTo(ConsortiumRegistration::class, 'consortium_registration_id')->withTrashed();
    }

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id')->withTrashed();
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function movedBy()
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
