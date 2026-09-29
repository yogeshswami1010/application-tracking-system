<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateCall extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['audio_path'];
    protected $casts = ['recording_consent_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
