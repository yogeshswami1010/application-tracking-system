<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateClientReviewMessage extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['read_at' => 'datetime', 'notification_sent_at' => 'datetime', 'notification_skipped_at' => 'datetime'];
    public function review() { return $this->belongsTo(CandidateClientReview::class, 'candidate_client_review_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
