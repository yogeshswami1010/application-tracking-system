<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobClientNote extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}