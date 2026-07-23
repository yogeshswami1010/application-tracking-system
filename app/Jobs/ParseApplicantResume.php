<?php

namespace App\Jobs;

use App\Services\ApplicantResumeParser;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParseApplicantResume
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public int $applicationId)
    {
    }

    public function handle(ApplicantResumeParser $parser): void
    {
        $parser->parse($this->applicationId);
    }
}
