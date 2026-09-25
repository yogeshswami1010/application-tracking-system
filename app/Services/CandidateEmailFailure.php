<?php

namespace App\Services;

use Throwable;

class CandidateEmailFailure
{
    // Return actionable descriptions without exposing SMTP credentials or protocol transcripts.
    public static function message(Throwable $error): string
    {
        $message = strtolower($error->getMessage());
        if (str_contains($message, 'ai search smtp is not configured')) {
            return 'AI Search email settings are incomplete. Configure the AI Search SMTP username, password, and sender address.';
        }
        if (str_contains($message, 'email settings are not configured')) {
            return 'Email settings are missing. Open Settings > Email Settings and configure SMTP.';
        }
        if (str_contains($message, 'candidate smtp settings are incomplete')) {
            return 'SMTP settings are incomplete. Set the server, port, and a valid sender email in Settings > Email Settings.';
        }
        if (str_contains($message, 'candidate mail driver is not smtp')) {
            return 'Email delivery requires SMTP. Select SMTP in Settings > Email Settings and enter your mail server settings.';
        }
        if (str_contains($message, 'authenticate') || str_contains($message, 'authentication') || preg_match('/\b(535|534)\b/', $message)) {
            return 'The mail server rejected the login. Check the SMTP username and password or app password in Email Settings.';
        }
        if (str_contains($message, 'certificate') || str_contains($message, 'ssl operation') || str_contains($message, 'crypto')) {
            return 'A secure connection to the mail server failed. Check the SMTP hostname, port, encryption, and server certificate.';
        }
        if (str_contains($message, 'connection') || str_contains($message, 'timed out') || str_contains($message, 'getaddrinfo') || str_contains($message, 'network is unreachable')) {
            return 'The ATS could not connect to the mail server. Check the SMTP host, port, and outbound connections on the hosting server.';
        }
        if (str_contains($message, 'relay') || str_contains($message, 'sender') || str_contains($message, 'send as')) {
            return 'The mail server rejected the sender. Use a verified sender address permitted by the SMTP account.';
        }
        if (preg_match('/\b(550|551|553|554)\b/', $message) || str_contains($message, 'recipient')) {
            return 'The mail server rejected this message or recipient. Verify the candidate email and the account’s sending restrictions.';
        }
        if (preg_match('/\b(421|450|451|452)\b/', $message) || str_contains($message, 'rate limit')) {
            return 'The mail server is temporarily unavailable or has limited sending. Check the provider status and sending quota.';
        }
        return 'Email failed. Ask your administrator to check the ATS log using the reference below for the mail server error.';
    }
}
