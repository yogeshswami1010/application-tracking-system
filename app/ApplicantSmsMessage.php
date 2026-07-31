<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicantSmsMessage extends Model
{
    protected $fillable = [
        'job_application_id',
        'user_id',
        'direction',
        'from_number',
        'to_number',
        'message',
        'telnyx_message_id',
        'status',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
