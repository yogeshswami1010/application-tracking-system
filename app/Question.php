<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = ['id'];

    public function jobs()
    {
        return $this->belongsToMany(Job::class, 'job_questions');
    }

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    public function answers()
    {
        return $this->hasMany(JobApplicationAnswer::class);
    }
}
