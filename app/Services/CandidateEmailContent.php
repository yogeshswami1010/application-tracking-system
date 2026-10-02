<?php
namespace App\Services;

final class CandidateEmailContent
{
    public static function presentation(string $body, bool $splitQuotes = true): array
    {
        $body = str_replace(["\r\n", "\r", "\u{2028}", "\u{2029}"], "\n", $body);
        $lines = explode("\n", $body);
        $boundary = null;
        if ($splitQuotes) {
            foreach ($lines as $index => $line) {
                // Gmail's attribution can wrap across several lines before wrote:.
                if (preg_match('/^\h*On\h+/i', $line)) {
                    $header = '';
                    for ($end = $index; $end < min(count($lines), $index + 10); $end++) {
                        $header .= ' '.trim($lines[$end]);
                        if (preg_match('/\bwrote:\h*$/i', $header)) { $boundary = $index; break 2; }
                    }
                }
                if (preg_match('/^\h*-{2,}\h*(?:Original Message|Forwarded message)\h*-{2,}\h*$/i', $line)) {
                    $boundary = $index; break;
                }
                if (preg_match('/^\h*From:\h*.+/i', $line)) {
                    $header = implode("\n", array_slice($lines, $index, 10));
                    if (preg_match('/^\h*(?:Sent|Date):/im', $header) && preg_match('/^\h*Subject:/im', $header)) {
                        $boundary = $index; break;
                    }
                }
                if (preg_match('/^\h*>/', $line)) { $boundary = $index; break; }
            }
        }
        $compact = static function (string $text): string {
            $text = preg_replace('/\h+$/m', '', $text);
            return trim(preg_replace('/\n{3,}/', "\n\n", $text));
        };
        if ($boundary !== null) {
            $reply = $compact(implode("\n", array_slice($lines, 0, $boundary)));
            if ($reply !== '') {
                $quoted = implode("\n", array_slice($lines, $boundary));
                $quoted = preg_replace('/^\h*(?:>\h*)+/m', '', $quoted);
                return ['reply' => $reply, 'quoted' => $compact($quoted)];
            }
        }
        // A message made entirely of quoted text may itself be the reply.
        return ['reply' => $compact($body), 'quoted' => ''];
    }

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
