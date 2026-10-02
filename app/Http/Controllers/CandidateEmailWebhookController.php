<?php
namespace App\Http\Controllers;
use App\CandidateEmailMessage;
use App\JobApplication;
use Illuminate\Http\Request;

class CandidateEmailWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless($request->header('X-Candidate-Email-Token') && hash_equals((string) config('services.candidate_email_webhook_token'), (string) $request->header('X-Candidate-Email-Token')), 401);
        $data = $request->validate(['from' => 'required|email', 'to' => 'required|string', 'subject' => 'nullable|string|max:191', 'text' => 'nullable|string', 'html' => 'nullable|string', 'message_id' => 'nullable|string|max:255', 'in_reply_to' => 'nullable|string|max:255']);
        $application = JobApplication::whereRaw('LOWER(email) = ?', [strtolower($data['from'])])->first();
        if (!$application) return response()->json(['accepted' => false, 'reason' => 'candidate_not_found'], 202);
        $message = CandidateEmailMessage::create(['job_application_id' => $application->id, 'direction' => 'inbound', 'from_address' => $data['from'], 'to_address' => $data['to'], 'subject' => $data['subject'] ?? '', 'body' => $data['html'] ?? ($data['text'] ?? ''), 'message_id' => $data['message_id'] ?? null, 'in_reply_to' => $data['in_reply_to'] ?? null, 'received_at' => now()]);
        return response()->json(['accepted' => true, 'id' => $message->id]);
    }
}
