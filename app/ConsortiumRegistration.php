<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsortiumRegistration extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth' => 'date',
        'eligible_to_work_canada' => 'boolean',
        'available_weekends' => 'boolean',
        'available_night_shifts' => 'boolean',
        'information_certified' => 'boolean',
        'agreement_accepted' => 'boolean',
        'sms_consent' => 'boolean',
        'reviewed_at' => 'datetime',
    ];
}