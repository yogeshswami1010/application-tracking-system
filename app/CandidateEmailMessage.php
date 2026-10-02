<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class CandidateEmailMessage extends Model
{
    protected $fillable = ['job_application_id','user_id','direction','from_address','to_address','subject','body','message_id','in_reply_to','read_at','received_at'];
    protected $casts = ['read_at' => 'datetime', 'received_at' => 'datetime'];
    public function application() { return $this->belongsTo(JobApplication::class, 'job_application_id')->withTrashed(); }
    public function user() { return $this->belongsTo(User::class); }
}
