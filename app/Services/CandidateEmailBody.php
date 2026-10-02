<?php
namespace App\Services;
final class CandidateEmailBody
{
    public static function render(string $message, ?string $signature): string
    {
        $escape = static fn (string $value): string => nl2br(htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        $html = '<div>'.$escape($message).'</div>';
        $signature = trim((string) $signature);
        if ($signature !== '') {
            $html .= '<div style="margin-top:24px;padding-top:12px;border-top:1px solid #e5e7eb;">'.$escape($signature).'</div>';
        }
        return $html;
    }
}
