<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\JobClientNote;
use Illuminate\Http\Request;

class AdminJobClientNoteController extends AdminBaseController
{
    public function store(Request $request)
    {
        abort_if(! $this->user->cans('edit_job_applications'), 403);

        $request->validate([
            'job_id'    => 'required|exists:jobs,id',
            'note_text' => 'required|string|max:5000',
        ]);

        JobClientNote::create([
            'job_id'    => $request->job_id,
            'user_id'   => $this->user->id,
            'note_text' => trim($request->note_text),
        ]);

        $notes = JobClientNote::with('user:id,name')
            ->where('job_id', $request->job_id)
            ->orderByDesc('created_at')
            ->get();

        $view = view('admin.job-applications.partials.client-notes-list', ['clientNotes' => $notes])->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function update(Request $request, $id)
    {
        abort_if(! $this->user->cans('edit_job_applications'), 403);

        $note = JobClientNote::findOrFail($id);
        $note->update(['note_text' => trim($request->note)]);

        $notes = JobClientNote::with('user:id,name')
            ->where('job_id', $note->job_id)
            ->orderByDesc('created_at')
            ->get();

        $view = view('admin.job-applications.partials.client-notes-list', ['clientNotes' => $notes])->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function destroy(Request $request, $id)
    {
        abort_if(! $this->user->cans('edit_job_applications'), 403);

        $note = JobClientNote::findOrFail($id);
        $jobId = $note->job_id;
        $note->delete();

        $notes = JobClientNote::with('user:id,name')
            ->where('job_id', $jobId)
            ->orderByDesc('created_at')
            ->get();

        $view = view('admin.job-applications.partials.client-notes-list', ['clientNotes' => $notes])->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }
}