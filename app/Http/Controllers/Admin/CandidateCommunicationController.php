<?php

namespace App\Http\Controllers\Admin;

use App\CandidateEmailTemplate;
use App\ConsortiumRegistration;
use App\JobApplication;
use App\ApplicantSmsMessage;
use App\SmsSetting;
use App\EmailSetting;
use App\Services\TelnyxSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CandidateCommunicationController extends AdminBaseController
{
    private function authorizeMessaging(): void
    {
        abort_unless($this->user->cans('view_job_applications') && $this->user->cans('edit_job_applications'), 403);
    }


    protected function mailer(string $source)
    {
        if ($source === 'ai-search') {
            $settings = config('mail.ai_search_smtp');
            if (empty($settings['username']) || empty($settings['password']) || empty($settings['from']['address'])) {
                throw new \RuntimeException('AI Search SMTP is not configured.');
            }
            $mailer = Mail::build($settings);
            $mailer->alwaysFrom($settings['from']['address'], $settings['from']['name'] ?? null);
            return $mailer;
        }
        $settings = EmailSetting::first();
        if (!$settings) throw new \RuntimeException('Email settings are not configured.');
        $mailer = Mail::build([
            'transport' => $settings->mail_driver,
            'host' => $settings->mail_host,
            'port' => $settings->mail_port,
            'encryption' => $settings->mail_encryption,
            'username' => $settings->mail_username,
            'password' => $settings->mail_password,
            'from' => ['address' => $settings->mail_from_email, 'name' => $settings->mail_from_name],
        ]);
        $mailer->alwaysFrom($settings->mail_from_email, $settings->mail_from_name);
        return $mailer;
    }

    public function templates()
    {
        $this->authorizeMessaging();
        return response()->json(['templates' => CandidateEmailTemplate::where('user_id', $this->user->id)
            ->orderBy('name')->get(['id', 'name', 'subject', 'message'])]);
    }

    public function saveTemplate(Request $request)
    {
        $this->authorizeMessaging();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'subject' => ['required', 'string', 'max:191'],
            'message' => ['required', 'string', 'max:10000'],
        ]);
        $template = CandidateEmailTemplate::updateOrCreate(
            ['user_id' => $this->user->id, 'name' => $data['name']],
            ['subject' => $data['subject'], 'message' => $data['message']]
        );
        return response()->json(['template' => $template, 'message' => 'Email template saved.']);
    }


    public function preview(Request $request, TelnyxSmsService $sms)
    {
        $this->authorizeMessaging();
        $data = $request->validate([
            'channel' => ['required', 'in:email,sms'],
            'recipients' => ['required', 'array', 'min:1', 'max:100'],
            'recipients.*.type' => ['required', 'in:application,registration'],
            'recipients.*.id' => ['required', 'integer', 'min:1'],
        ]);
        $seen = [];
        $recipients = [];
        foreach ($data['recipients'] as $recipient) {
            $registration = $recipient['type'] === 'registration';
            $candidate = $registration ? ConsortiumRegistration::find($recipient['id']) : JobApplication::withTrashed()->whereNull('moved_to_trash_at')->find($recipient['id']);
            $name = $candidate ? ($registration ? trim($candidate->first_name.' '.$candidate->last_name) : $candidate->full_name) : 'Unavailable candidate';
            $reason = null;
            $address = trim((string) ($data['channel'] === 'email' ? $candidate?->email : $candidate?->phone));
            try {
                if (!$candidate) $reason = 'Candidate is no longer available.';
                elseif ($data['channel'] === 'sms' && $registration && !$candidate->sms_consent) $reason = 'No SMS consent.';
                elseif (!$address) $reason = 'Missing contact details.';
                elseif ($data['channel'] === 'email' && !filter_var($address, FILTER_VALIDATE_EMAIL)) $reason = 'Invalid email address.';
                else {
                    $address = $data['channel'] === 'sms' ? $sms->normalizePhone($address) : strtolower($address);
                    if (isset($seen[$address])) $reason = 'Duplicate contact.';
                    $seen[$address] = true;
                }
            } catch (\RuntimeException $e) {
                $reason = 'Invalid phone number.';
            }
            $recipients[] = $recipient + ['name' => $name, 'address' => $address, 'reason' => $reason];
        }
        return response()->json(['recipients' => $recipients]);
    }

    public function send(Request $request, TelnyxSmsService $sms)
    {
        $this->authorizeMessaging();
        $data = $request->validate([
            'channel' => ['required', 'in:email,sms'],
            'recipients' => ['required', 'array', 'min:1', 'max:100'],
            'recipients.*.type' => ['required', 'in:application,registration'],
            'recipients.*.id' => ['required', 'integer', 'min:1'],
            'source' => ['nullable', 'in:ai-search,candidates'],
            'subject' => ['required_if:channel,email', 'nullable', 'string', 'max:191'],
            'message' => ['required', 'string', $request->input('channel') === 'sms' ? 'max:1600' : 'max:10000'],
        ]);
        $results = [];
        $seen = [];
        foreach ($data['recipients'] as $recipient) {
            $registration = $recipient['type'] === 'registration';
            $candidate = $registration ? ConsortiumRegistration::find($recipient['id']) : JobApplication::withTrashed()->whereNull('moved_to_trash_at')->find($recipient['id']);
            $result = $recipient + ['status' => 'skipped', 'reason' => 'Candidate is no longer available.'];
            if (!$candidate) { $results[] = $result; continue; }
            if ($data['channel'] === 'sms' && $registration && !$candidate->sms_consent) {
                $result['reason'] = 'SMS consent has not been given.';
                $results[] = $result;
                continue;
            }
            $address = trim((string) ($data['channel'] === 'email' ? $candidate->email : $candidate->phone));
            if (!$address || ($data['channel'] === 'email' && !filter_var($address, FILTER_VALIDATE_EMAIL))) {
                $result['reason'] = 'Missing or invalid '.$data['channel'].' contact.';
                $results[] = $result;
                continue;
            }
            try {
                $address = $data['channel'] === 'sms' ? $sms->normalizePhone($address) : strtolower($address);
                if (isset($seen[$address])) {
                    $result['reason'] = 'Duplicate contact.';
                    $results[] = $result;
                    continue;
                }
                $seen[$address] = true;
                $name = $registration ? trim($candidate->first_name.' '.$candidate->last_name) : $candidate->full_name;
                $personalize = fn ($text) => str_ireplace(['{{applicant_name}}', '[applicant_name]', '%applicant_name%'], $name ?: 'Applicant', $text);
                $message = $personalize($data['message']);
                if ($data['channel'] === 'email') {
                    $this->mailer($data['source'] ?? 'candidates')->html('<div>'.nl2br(e($message)).'</div>', function ($mail) use ($address, $name, $data, $personalize) {
                        $mail->to($address, $name)->subject($personalize($data['subject']));
                    });
                } else {
                    if (mb_strlen($message) > 1600) {
                        throw new \RuntimeException('Personalized SMS exceeds 1600 characters.');
                    }
                    $messageId = $sms->send($address, $message);
                    if (!$registration) {
                        // A history write failure must not report an accepted SMS as unsent.
                        try {
                            ApplicantSmsMessage::create([
                                'job_application_id' => $candidate->id, 'user_id' => $this->user->id,
                                'direction' => 'outbound', 'from_number' => $sms->normalizePhone((string) SmsSetting::first()->telnyx_from_number),
                                'to_number' => $address, 'message' => $message,
                                'telnyx_message_id' => $messageId, 'status' => 'sent',
                            ]);
                        } catch (\Throwable $e) {
                            Log::warning('Bulk SMS history could not be stored.', ['candidate_id' => $candidate->id]);
                        }
                    }
                }
                $result['status'] = 'sent';
                $result['reason'] = 'Accepted by the messaging provider.';
            } catch (\Throwable $e) {
                Log::warning('Candidate bulk message failed.', ['type' => $recipient['type'], 'id' => $candidate->id, 'error' => $e->getMessage()]);
                $result['status'] = 'failed';
                $result['reason'] = 'Could not send. Check the contact details and messaging settings.';
            }
            $results[] = $result;
        }
        return response()->json(['results' => $results]);
    }
}
