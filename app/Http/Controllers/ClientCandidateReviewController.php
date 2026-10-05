<?php
namespace App\Http\Controllers;

use App\CandidateClientReview;
use App\CompanySetting;
use App\Services\CandidateClientReviewService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// A standalone client page; never loads the ATS dashboard or internal profile data.
class ClientCandidateReviewController extends BaseController
{
    private function available(CandidateClientReview $review): void
    {
        abort_unless($review->isAvailable(), 410, 'This candidate review link has expired or is no longer available.');
    }

    public function show(CandidateClientReview $review, CandidateClientReviewService $service)
    {
        $this->available($review);
        $company = CompanySetting::first(['company_name', 'logo']);
        $messages = $review->messages()->with('user:id,name')
            ->where('submission_id', '!=', $review->public_id)
            ->where(fn ($query) => $query->where('direction', 'inbound')->orWhere('mail_status', 'sent'))
            ->orderBy('id')->get();
        return response()->view('client-reviews.show', [
            'review' => $review, 'messages' => $messages, 'resumeUrl' => $service->url($review, 'resume'),
            'replyUrl' => $service->url($review, 'reply'),
            'companyLogoUrl' => $company?->logo_url ?? asset('app-logo.png'),
            'companyName' => $company?->company_name ?: 'Company',
        ])->withHeaders([
            'Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive', 'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; frame-src 'self'; script-src 'none'; form-action 'self'; base-uri 'none'; frame-ancestors 'self'",
        ]);
    }

    public function resume(CandidateClientReview $review)
    {
        $this->available($review);
        abort_unless(Storage::exists($review->resumePath()), 404, 'The shared CV is no longer available.');
        $name = str_replace(['"', "\r", "\n"], '', $review->resume_original_name);
        $mime = Storage::mimeType($review->resumePath());
        $inline = in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'text/plain'], true);
        return Storage::response($review->resumePath(), $name, [
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.$name.'"',
            'Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function reply(Request $request, CandidateClientReview $review, CandidateClientReviewService $service)
    {
        $this->available($review);
        $data = $request->validate(['message' => ['required', 'string', 'max:10000'], 'submission_id' => ['required', 'uuid']]);
        $message = DB::transaction(function () use ($review, $data) {
            $locked = CandidateClientReview::lockForUpdate()->findOrFail($review->id);
            $this->available($locked);
            $message = $locked->messages()->firstOrCreate(['submission_id' => $data['submission_id']], [
                'direction' => 'inbound', 'body_text' => trim($data['message']), 'mail_status' => 'received',
            ]);
            abort_unless($message->direction === 'inbound', 409);
            return $message;
        });
        try {
            $service->notify($review, $message);
        } catch (\Throwable $error) {
            // The review is safely in ATS even when the email notification fails.
            report($error);
        }
        return redirect($service->url($review))->with('review_sent', 'Your review has been sent to the recruitment team.');
    }
}
