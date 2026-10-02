<?php
namespace App\Services;

final class CandidateEmailContent
{
    public static function decode(string $text, int $encoding = 0, string $charset = 'UTF-8'): string
    {
        if ($encoding === 3) $text = base64_decode($text, true) ?: '';
        elseif ($encoding === 4) $text = quoted_printable_decode($text);
        try { $text = mb_convert_encoding($text, 'UTF-8', $charset ?: 'UTF-8'); }
        catch (\ValueError $e) { $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8'); }
        return $text;
    }

    public static function plain(string $html): string
    {
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $html);
        $html = preg_replace('/<br\s*\/?\s*>|<\/(?:div|p|tr|li|h[1-6])\s*>/i', "\n", $html);
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    public static function body(object $structure, callable $fetch): string
    {
        $plain = []; $html = [];
        $walk = function ($part, $section) use (&$walk, &$plain, &$html, $fetch) {
            if (strtoupper($part->disposition ?? '') === 'ATTACHMENT') return;
            foreach (array_merge($part->parameters ?? [], $part->dparameters ?? []) as $param) {
                if (in_array(strtolower($param->attribute), ['filename', 'name'], true)) return;
            }
            if (isset($part->parts)) {
                foreach ($part->parts as $index => $child) $walk($child, $section === '' ? (string) ($index + 1) : $section.'.'.($index + 1));
                return;
            }
            if (($part->type ?? 0) !== 0) return;
            $charset = 'UTF-8';
            foreach ($part->parameters ?? [] as $param) if (strtolower($param->attribute) === 'charset') $charset = $param->value;
            $value = self::decode((string) $fetch($section), (int) ($part->encoding ?? 0), $charset);
            if (strtoupper($part->subtype ?? 'PLAIN') === 'PLAIN') $plain[] = $value;
            elseif (strtoupper($part->subtype ?? '') === 'HTML') $html[] = self::plain($value);
        };
        $walk($structure, '');
        return trim(implode("\n", $plain ?: $html));
    }
}
