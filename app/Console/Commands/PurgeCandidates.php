<?php

namespace App\Console\Commands;

use App\ApplicationSetting;
use App\Helper\Files;
use App\InterviewSchedule;
use App\JobApplication;
use App\JobApplicationAnswer;
use App\Onboard;
use App\OnboardAnswer;
use App\Document;
use App\ZoomMeeting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PurgeCandidates extends Command
{
    protected $signature = 'candidates:purge {--dry-run=0 : If set, only reports what would be deleted} {--limit=0 : Limit number of candidates to process (0 = no limit)}';

    protected $description = 'Permanently deletes old candidate data (GDPR retention)';

    public function handle(): int
    {
        $setting = ApplicationSetting::first();
        $enabled = (bool) ($setting?->auto_delete_candidates ?? false);
        $days = (int) ($setting?->candidate_retention_days ?? 0);

        if (! $enabled || $days <= 0) {
            $this->info('Candidate purge is disabled (enable it in Application Settings).');
            return self::SUCCESS;
        }

        $dryRun = (int) $this->option('dry-run') === 1;
        $limit = (int) $this->option('limit') ?: 0;
        $threshold = now()->subDays($days);

        $query = JobApplication::withTrashed()->where('created_at', '<', $threshold);

        if ($limit > 0) {
            $candidates = $query->limit($limit)->get(['id', 'photo', 'created_at']);
        } else {
            $candidates = null;
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('No candidates found for purge.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Candidate purge: %d candidates older than %d days. %s',
            $total,
            $days,
            $dryRun ? 'Dry-run mode' : 'Deletion enabled'
        ));

        if ($candidates) {
            $this->purgeCollection($candidates, $dryRun);
            return self::SUCCESS;
        }

        $query->orderBy('id')->chunkById(50, function ($chunk) use ($dryRun) {
            $this->purgeCollection($chunk, $dryRun);
        }, 'id');

        return self::SUCCESS;
    }

    /**
     * @param iterable<JobApplication> $candidates
     */
    private function purgeCollection(iterable $candidates, bool $dryRun): void
    {
        foreach ($candidates as $candidate) {
            $this->purgeCandidate($candidate, $dryRun);
        }
    }

    private function purgeCandidate(JobApplication $candidate, bool $dryRun): void
    {
        $id = $candidate->id;

        try {
            $meetingIds = InterviewSchedule::where('job_application_id', $id)
                ->whereNotNull('meeting_id')
                ->pluck('meeting_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (! empty($meetingIds)) {
                if ($dryRun) {
                    $this->line("Candidate #{$id}: would delete " . count($meetingIds) . ' zoom meeting(s).');
                } else {
                    ZoomMeeting::whereIn('id', $meetingIds)->delete();
                }
            }

            if (! empty($candidate->photo)) {
                if ($dryRun) {
                    $this->line("Candidate #{$id}: would delete candidate photo.");
                } else {
                    Storage::delete('candidate-photos/' . $candidate->photo);
                }
            }

            $documents = Document::where('documentable_type', JobApplication::class)
                ->where('documentable_id', $id)
                ->get(['id', 'hashname', 'name']);

            if (! $documents->isEmpty()) {
                if ($dryRun) {
                    $this->line("Candidate #{$id}: would delete " . $documents->count() . ' document file(s).');
                } else {
                    foreach ($documents as $document) {
                        if (! empty($document->hashname)) {
                            Files::deleteFile($document->hashname, 'documents/' . $id);
                        }
                        $document->delete();
                    }
                }
            }

            $answerFiles = JobApplicationAnswer::where('job_application_id', $id)
                ->whereNotNull('file')
                ->pluck('file')
                ->filter()
                ->all();

            if (! empty($answerFiles)) {
                if ($dryRun) {
                    $this->line("Candidate #{$id}: would delete " . count($answerFiles) . ' answer file(s).');
                } else {
                    foreach ($answerFiles as $file) {
                        Files::deleteFile($file, 'documents');
                    }
                }
            }

            $onboards = Onboard::where('job_application_id', $id)->with(['files'])->get(['id']);
            foreach ($onboards as $onboard) {
                foreach ($onboard->files as $onboardFile) {
                    if (empty($onboardFile->hash_name)) {
                        continue;
                    }

                    if ($dryRun) {
                        $this->line("Candidate #{$id}: would delete onboard file.");
                    } else {
                        Files::deleteFile($onboardFile->hash_name, 'onboard-files');
                    }
                }

                $onboardAnswerFiles = OnboardAnswer::where('onboard_id', $onboard->id)
                    ->whereNotNull('file')
                    ->pluck('file')
                    ->filter()
                    ->all();

                if (! empty($onboardAnswerFiles)) {
                    if ($dryRun) {
                        $this->line("Candidate #{$id}: would delete " . count($onboardAnswerFiles) . ' onboard answer file(s).');
                    } else {
                        foreach ($onboardAnswerFiles as $file) {
                            Files::deleteFile($file, 'documents');
                        }
                    }
                }
            }

            if ($dryRun) {
                $this->line("Candidate #{$id}: would force-delete job application record.");
                return;
            }

            // Zoom meeting deletions happen above (no DB cascade from schedules -> meetings).
            // DB-level cascades remove related rows (notes/interviews/onboard/app answers).
            $candidate->forceDelete();
        } catch (Throwable $e) {
            $this->error("Candidate purge failed for #{$id}: {$e->getMessage()}");
        }
    }
}

