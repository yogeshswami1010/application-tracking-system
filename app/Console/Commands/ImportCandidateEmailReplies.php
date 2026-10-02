<?php
namespace App\Console\Commands;

use App\CandidateEmailMessage;
use App\JobApplication;
use Illuminate\Console\Command;

class ImportCandidateEmailReplies extends Command
{
    protected $signature = 'candidate-emails:import-replies';
    protected $description = 'Import candidate replies from the configured IMAP mailbox';

    public function handle(): int
    {
        if (!function_exists('imap_open')) {
            $this->error('PHP IMAP extension is not installed.');
            return self::FAILURE;
        }
        $mailbox = sprintf('{%s:%s/imap/ssl}INBOX', env('AI_SEARCH_MAIL_IMAP_HOST', 'imappro.zoho.in'), env('AI_SEARCH_MAIL_IMAP_PORT', 993));
        $inbox = @imap_open($mailbox, env('AI_SEARCH_MAIL_USERNAME'), env('AI_SEARCH_MAIL_PASSWORD'));
        if (!$inbox) { $this->error(imap_last_error() ?: 'Could not connect to the mailbox.'); return self::FAILURE; }
        $count = 0;
        foreach (imap_search($inbox, 'UNSEEN') ?: [] as $number) {
            $header = imap_headerinfo($inbox, $number);
            $from = strtolower(trim(($header->from[0]->mailbox ?? '').'@'.($header->from[0]->host ?? '')));
            $application = JobApplication::whereRaw('LOWER(email) = ?', [$from])->first();
            if (!$application) { imap_setflag_full($inbox, (string) $number, '\\Seen'); continue; }
            $messageId = trim((string) ($header->message_id ?? '')) ?: null;
            if ($messageId && CandidateEmailMessage::where('message_id', $messageId)->exists()) { imap_setflag_full($inbox, (string) $number, '\\Seen'); continue; }
            $body = imap_fetchbody($inbox, $number, '1');
            if (strtolower($header->encoding ?? '') === 'base64') $body = base64_decode($body) ?: $body;
            if (strtolower($header->encoding ?? '') === 'quoted-printable') $body = quoted_printable_decode($body);
            CandidateEmailMessage::create(['job_application_id' => $application->id, 'direction' => 'inbound', 'from_address' => $from, 'to_address' => env('AI_SEARCH_MAIL_FROM_ADDRESS'), 'subject' => (string) ($header->subject ?? ''), 'body' => trim($body), 'message_id' => $messageId, 'in_reply_to' => trim((string) ($header->references ?? '')) ?: null, 'received_at' => now()]);
            imap_setflag_full($inbox, (string) $number, '\\Seen');
            $count++;
        }
        imap_close($inbox);
        $this->info("Imported {$count} candidate email replies.");
        return self::SUCCESS;
    }
}
