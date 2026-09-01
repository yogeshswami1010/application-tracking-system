<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsortiumTempStaffingHistory extends Model
{
    protected $guarded = ['id'];

    public function registration()
    {
        return $this->belongsTo(ConsortiumRegistration::class, 'consortium_registration_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}