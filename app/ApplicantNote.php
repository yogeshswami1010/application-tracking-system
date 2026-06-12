<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantNote extends Model
{
    protected $fillable = [
        'note_text',
        'user_id',
        'job_application_id',
        'mentions',
    ];

    protected $casts = [
        'mentions' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }
}