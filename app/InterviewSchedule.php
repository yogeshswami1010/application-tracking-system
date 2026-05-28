<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InterviewSchedule extends Model
{
    protected $casts = [
        'schedule_date' => 'datetime',
    ];

    // Relation with job application
    public function jobApplication(){
        return $this->belongsTo(JobApplication::class);
    }

    // Relation with user
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function meeting(){
        return $this->belongsTo(ZoomMeeting::class, 'meeting_id');
    }
    // Relation with user
    public function comments(){
        return $this->hasMany(ScheduleComments::class);
    }

    // Relation with user
    public function employee(){
        return $this->hasMany(InterviewScheduleEmployee::class);
    }

    public function employees(){
        return $this->belongsToMany(User::class, InterviewScheduleEmployee::class);
    }

    public function employeeData($userId){
        return InterviewScheduleEmployee::where('user_id', $userId)->where('interview_schedule_id', $this->id)->first();
    }
}
