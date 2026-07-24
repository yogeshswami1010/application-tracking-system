<?php

namespace App\Http\Controllers;

use App\ApplicantSmsMessage;
use App\JobApplication;
use App\Services\TelnyxSmsService;
use App\SmsSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelnyxWebhookController extends Controller
{
    public function __invoke(Request $request, TelnyxSmsService $sms): Response
    {
        $settings = SmsSetting::first();
        $publicKey = trim((string) optional($settings)->telnyx_public_key);

        if ($publicKey === '' || ! $this->hasValidSignature($request, $publicKey)) {
            Log::warning('Rejected an unsigned or invalid Telnyx webhook.');
            return response('Invalid signature', 401);
        }

        $eventType = (string) $request->input('data.event_type');
        $payload = (array) $request->input('data.payload', []);
        $messageId = (string) ($payload['id'] ?? '');

        if ($eventType !== 'message.received' || $messageId === '') {
            return response('OK');
        }

        if (ApplicantSmsMessage::where('telnyx_message_id', $messageId)->exists()) {
            return response('OK');
        }

        $from = (string) data_get($payload, 'from.phone_number', '');
        $to = (string) data_get($payload, 'to.0.phone_number', '');
        $message = trim((string) ($payload['text'] ?? ''));

        try {
            $normalizedFrom = $sms->normalizePhone($from);
            $normalizedTo = $sms->normalizePhone($to);
        } catch (\Throwable $e) {
            Log::warning('Telnyx reply contains an invalid phone number.', ['message_id' => $messageId]);
            return response('OK');
        }

        $application = JobApplication::withTrashed()
            ->whereNotNull('phone')
            ->latest('id')
            ->get(['id', 'phone'])
            ->first(function (JobApplication $candidate) use ($sms, $normalizedFrom) {
                try {
                    return $sms->normalizePhone((string) $candidate->phone) === $normalizedFrom;
                } catch (\Throwable $e) {
                    return false;
                }
            });

        if (! $application) {
            Log::notice('Telnyx reply could not be matched to an applicant.', [
                'from' => $normalizedFrom,
                'message_id' => $messageId,
            ]);
            return response('OK');
        }

        ApplicantSmsMessage::create([
            'job_application_id' => $application->id,
            'direction' => 'inbound',
            'from_number' => $normalizedFrom,
            'to_number' => $normalizedTo,
            'message' => $message,
            'telnyx_message_id' => $messageId,
            'status' => 'received',
            'received_at' => now(),
        ]);

        return response('OK');
    }

    private function hasValidSignature(Request $request, string $configuredKey): bool
    {
        if (! function_exists('sodium_crypto_sign_verify_detached')) {
            Log::error('PHP Sodium is required to verify Telnyx webhooks.');
            return false;
        }

        $signature = base64_decode((string) $request->header('telnyx-signature-ed25519'), true);
        $timestamp = (string) $request->header('telnyx-timestamp');
        if ($signature === false || $timestamp === '' || abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $key = ctype_xdigit($configuredKey) && strlen($configuredKey) === 64
            ? hex2bin($configuredKey)
            : base64_decode($configuredKey, true);

        return is_string($key)
            && strlen($key) === SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
            && sodium_crypto_sign_verify_detached($signature, $timestamp.'|'.$request->getContent(), $key);
    }
}
