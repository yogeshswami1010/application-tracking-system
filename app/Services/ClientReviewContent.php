<?php
namespace App\Services;

use Illuminate\Validation\ValidationException;

final class ClientReviewContent
{
    public static function clean(string $html): string
    {
        // Reuse the signature allowlist, retaining only message-formatting elements.
        return strip_tags(EmailSignatureHtml::clean($html), '<div><span><p><br><strong><b><em><i><u><a><ul><ol><li><font><small><sub><sup><h1><h2><h3><h4><h5><h6>');
    }

    public static function decode(string $encoded): string
    {
        $html = base64_decode($encoded, true);
        if ($html === false || !mb_check_encoding($html, 'UTF-8') || strlen($html) > 50000) {
            throw ValidationException::withMessages(['message_payload' => 'The formatted message is invalid or too long.']);
        }
        $html = self::clean($html);
        if (!preg_match('/[^\s\x{00A0}]/u', CandidateEmailContent::plain($html))) {
            throw ValidationException::withMessages(['message_payload' => 'Write a message for the client.']);
        }
        return $html;
    }
}
