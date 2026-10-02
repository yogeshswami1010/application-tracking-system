<?php
namespace App\Services;
final class CandidateEmailBody
{
    public static function render(string $message, ?string $signature, ?string $imageUrl = null, ?string $signatureHtml = null): string
    {
        $escape = static fn (string $value): string => nl2br(htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        $html = '<div>'.$escape($message).'</div>';
        $formatted = EmailSignatureHtml::clean($signatureHtml);
        if ($formatted !== '') return $html.'<div style="margin-top:24px;">'.$formatted.'</div>';
        $signature = trim((string) $signature);
        if ($signature !== '') {
            $html .= '<div style="margin-top:24px;padding-top:12px;border-top:1px solid #e5e7eb;">'.$escape($signature).'</div>';
        }
        if ($imageUrl && filter_var($imageUrl, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($imageUrl, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)) {
            $src = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<div style="margin-top:16px;"><img src="'.$src.'" alt="Email signature" width="500" style="display:block;width:500px;max-width:100%;height:auto;border:0;"></div>';
        }
        return $html;
    }
}
