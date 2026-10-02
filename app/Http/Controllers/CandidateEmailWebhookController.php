<?php
namespace App\Http\Controllers;
use App\CandidateEmailMessage;
use App\JobApplication;
use App\Services\CandidateEmailThread;
use App\Services\CandidateEmailContent;
use Illuminate\Http\Request;

class CandidateEmailWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless($request->header('X-Candidate-Email-Token') && hash_equals((string) config('services.candidate_email_webhook_token'), (string) $request->header('X-Candidate-Email-Token')), 401);
        $data = $request->validate(['from' => 'required|email', 'to' => 'required|string', 'subject' => 'nullable|string|max:191', 'text' => 'nullable|string', 'html' => 'nullable|string', 'message_id' => 'nullable|string|max:255', 'in_reply_to' => 'nullable|string|max:255']);
        $sent = CandidateEmailMessage::where('direction', 'outbound')->whereRaw('LOWER(TRIM(to_address)) = ?', [strtolower($data['from'])])
            ->latest('id')->get(['job_application_id','subject','message_id'])->toArray();
        $applicationId = CandidateEmailThread::match($sent, $data['in_reply_to'] ?? '', '', $data['subject'] ?? '');
        if (!$applicationId) {
            $ids = JobApplication::whereRaw('LOWER(TRIM(email)) = ?', [strtolower($data['from'])])->limit(2)->pluck('id');
            if ($ids->count() === 1) $applicationId = $ids->first();
        }
        if (!$applicationId) return response()->json(['accepted' => false, 'reason' => 'candidate_not_found_or_ambiguous'], 202);
        $messageId = CandidateEmailThread::ids($data['message_id'] ?? '')[0] ?? null;
        $existing = $messageId ? CandidateEmailMessage::where('direction', 'inbound')->whereIn('message_id', [$messageId, '<'.$messageId.'>'])->first() : null;
        if ($existing) return response()->json(['accepted' => true, 'id' => $existing->id]);
        $message = CandidateEmailMessage::create(['job_application_id' => $applicationId, 'direction' => 'inbound', 'from_address' => $data['from'], 'to_address' => $data['to'], 'subject' => $data['subject'] ?? '', 'body' => $data['text'] ?? CandidateEmailContent::plain($data['html'] ?? ''), 'message_id' => $messageId, 'in_reply_to' => CandidateEmailThread::ids($data['in_reply_to'] ?? '')[0] ?? null, 'received_at' => now()]);
        return response()->json(['accepted' => true, 'id' => $message->id]);
    }
}
