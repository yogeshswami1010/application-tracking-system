<?php
namespace App\Console\Commands;

use App\CandidateClientReviewMessage;
use App\Services\CandidateClientReviewService;
use Illuminate\Console\Command;

class NotifyCandidateClientReviews extends Command
{
    protected $signature = 'client-reviews:notify';
    protected $description = 'Retry pending email notifications for client reviews already saved in ATS';
    public function handle(CandidateClientReviewService $service): int
    {
        $sent = 0;
        $messages = CandidateClientReviewMessage::with('review.user')->where('direction', 'inbound')
            ->whereNull('notification_sent_at')->orderBy('id')->limit(100)->get();
        foreach ($messages as $message) {
            try { $service->notify($message->review, $message); $sent++; }
            catch (\Throwable $error) { report($error); }
        }
        $this->info('Sent '.$sent.' client review notifications.');
        return self::SUCCESS;
    }
}
