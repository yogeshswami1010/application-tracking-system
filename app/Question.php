<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = ['id'];

    // ✅ Add this cast so is_knockout always returns true/false
    protected $casts = [
        'is_knockout' => 'boolean',
    ];

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