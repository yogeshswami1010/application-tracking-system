<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ApplicantNote\StoreNote;
use App\ApplicantNote;
use App\Helper\Reply;
use App\User;
use App\Notifications\ApplicantNoteMention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ApplicantNoteController extends AdminBaseController
{
    public function store(StoreNote $request)
    {
        $note = new ApplicantNote();
        $note->note_text = $request->note;
        $note->user_id   = auth()->id();
        $note->job_application_id = $request->id;

        // Extract @mentions
        $mentionedIds = $this->extractAndStoreMentions($request->note, $note);
        $note->mentions = !empty($mentionedIds) ? json_encode(array_values($mentionedIds)) : null;
        $note->save();

        // Send notifications
        $this->sendMentionNotifications($note, $mentionedIds);

        $notes = ApplicantNote::with('user:id,name')
            ->where('job_application_id', $request->id)
            ->orderByDesc('created_at')
            ->get();

        $user = auth()->user();

        $view = view('admin.job-applications.partials.applicant-notes-list', compact('notes', 'user'))->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function update(Request $request, $id)
    {
        $note = ApplicantNote::findOrFail($id);

        // Only the note's own author can edit
        abort_if(auth()->id() !== $note->user_id, 403);

        $note->note_text = $request->note;

        // Re-extract mentions on edit
        $mentionedIds = $this->extractAndStoreMentions($request->note, $note);
        $note->mentions = !empty($mentionedIds) ? json_encode(array_values($mentionedIds)) : null;
        $note->save();

        // Send notifications for newly mentioned users
        $this->sendMentionNotifications($note, $mentionedIds);

        $notes = ApplicantNote::with('user:id,name')
            ->where('job_application_id', $note->job_application_id)
            ->orderByDesc('created_at')
            ->get();

        $user = auth()->user();

        $view = view('admin.job-applications.partials.applicant-notes-list', compact('notes', 'user'))->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function destroy($id)
{
    abort_if(!auth()->user()->hasRole('admin'), 403);

    $note = ApplicantNote::findOrFail($id);
    $jobApplicationId = $note->job_application_id;
    $note->delete();

    $notes = ApplicantNote::with('user:id,name')
        ->where('job_application_id', $jobApplicationId)
        ->orderByDesc('created_at')
        ->get();

    $view = view('admin.job-applications.partials.applicant-notes-list', compact('notes'))->render();

    return Reply::dataOnly(['status' => 'success', 'view' => $view]);
}

    // ── Helpers ──────────────────────────────────────────────

    private function extractAndStoreMentions(string $text, ApplicantNote $note): array
    {
        preg_match_all('/@([a-zA-Z0-9_]+)/', $text, $matches);
        if (empty($matches[1])) return [];

        $mentionedIds = [];
        foreach ($matches[1] as $username) {
            $user = User::where('name', 'LIKE', $username . '%')
                ->orWhere('name', 'LIKE', '%' . $username . '%')
                ->first();
            if ($user && $user->id !== auth()->id()) {
                $mentionedIds[] = $user->id;
            }
        }

        return array_unique($mentionedIds);
    }

    private function sendMentionNotifications(ApplicantNote $note, array $userIds): void
    {
        if (empty($userIds)) return;

        $users = User::whereIn('id', $userIds)->get();
        foreach ($users as $user) {
            $user->notify(new ApplicantNoteMention($note));
        }
    }
}