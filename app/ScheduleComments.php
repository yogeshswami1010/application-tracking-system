<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleComments extends Model
{
    protected $guarded = ['id'];
    
    protected $casts = [
        'created_at' => 'datetime',
    ];
    protected $table = 'interview_schedule_comments';

    // Relation with job application
    public function jobApplication(){
        return $this->belongsTo(InterviewSchedule::class);
    }

    // Relation with user
    public function user(){
        return $this->belongsTo(User::class);
    }


}
