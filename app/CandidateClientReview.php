<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateClientReview extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['sent_at' => 'datetime', 'expires_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function application() { return $this->belongsTo(JobApplication::class, 'job_application_id')->withTrashed(); }
    public function user() { return $this->belongsTo(User::class); }
    public function messages() { return $this->hasMany(CandidateClientReviewMessage::class); }

    public function isAvailable(): bool
    {
        return $this->sent_at !== null && $this->revoked_at === null && $this->expires_at->isFuture()
            && $this->application !== null && $this->application->moved_to_trash_at === null;
    }

    public function resumePath(): string
    {
        return 'documents/'.$this->job_application_id.'/'.basename($this->resume_hashname);
    }
}
