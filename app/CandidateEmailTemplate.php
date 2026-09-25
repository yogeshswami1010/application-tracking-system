<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateEmailTemplate extends Model
{
    protected $fillable = ['user_id', 'name', 'subject', 'message'];
}
