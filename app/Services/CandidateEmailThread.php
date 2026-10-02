<?php
namespace App\Services;

final class CandidateEmailThread
{
    public static function ids(?string $header): array
    {
        preg_match_all('/<([^<>\s]+)>/', (string) $header, $matches);
        $ids = $matches[1];
        if (!$ids && trim((string) $header) !== '') $ids = preg_split('/\s+/', trim($header));
        return array_values(array_unique(array_filter(array_map(static fn ($id) => trim($id, "<> \t\r\n"), $ids))));
    }

    public static function subject(?string $subject): string
    {
        $subject = trim((string) $subject);
        do {
            $before = $subject;
            $subject = preg_replace('/^\s*(?:re|fw|fwd)\s*:\s*/iu', '', $subject);
        } while ($before !== $subject);
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($subject)));
    }

    /** The caller supplies only sent messages to the reply sender, newest first. */
    public static function match(array $sent, ?string $inReplyTo, ?string $references, ?string $subject): ?int
    {
        $parents = array_merge(array_reverse(self::ids($inReplyTo)), array_reverse(self::ids($references)));
        foreach ($parents as $parent) {
            foreach ($sent as $message) {
                if (in_array($parent, self::ids($message['message_id'] ?? ''), true)) return (int) $message['job_application_id'];
            }
        }
        // Legacy emails had no Message-ID. Use an exact reply subject only when
        // all matching outbound records belong to the same candidate profile.
        $target = self::subject($subject);
        if ($target === '') return null;
        $profiles = [];
        foreach ($sent as $message) {
            if (self::subject($message['subject'] ?? '') === $target) $profiles[] = (int) $message['job_application_id'];
        }
        $profiles = array_values(array_unique($profiles));
        return count($profiles) === 1 ? $profiles[0] : null;
    }
}
