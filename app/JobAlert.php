<?php

namespace App;

use App\JobType;
use App\JobCategory;
use App\JobLocation;
use App\WorkExperience;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class JobAlert extends Model
{
    use Notifiable;
    
    protected $guarded = ['id'];

    public function workExperience()
    {
        return $this->belongsTo(WorkExperience::class, 'work_experience_id');
    }

    public function  jobType()
    {
        return $this->belongsTo(JobType::class, 'job_type_id');
    }

    public function alertCategory()
    {
        return $this->belongsToMany(JobCategory::class, 'job_alert_categories', 'job_alert_id', 'category_id');
    }

    public function alertLocation()
    {
        return $this->belongsToMany(JobLocation::class, 'job_alert_locations', 'job_alert_id', 'location_id');
    }

    public function alertLocationData(){
        return $this->belongsToMany(JobLocation::class)->withPivot('job_alert_id', 'job_location_id');
    }

    

}
