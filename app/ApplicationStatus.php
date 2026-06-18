<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicationStatus extends Model
{
    protected $table = 'application_status';

    protected $fillable = ['job_id', 'status', 'color', 'position'];

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'status_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function scopeStatus($query, $type)
    {
        return $query->where('status', $type)->first();
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('job_id');
    }

    public function scopeForJob($query, int $jobId)
    {
        return $query->where('job_id', $jobId);
    }
}