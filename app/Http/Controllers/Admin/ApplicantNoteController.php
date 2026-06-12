<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ApplicantNote\StoreNote;
use App\ApplicantNote;
use App\Helper\Reply;
use Illuminate\Http\Request;

class ApplicantNoteController extends AdminBaseController
{
    public function store(StoreNote $request)
    {
        $note = new ApplicantNote();
        $note->note_text = $request->note;
        $note->user_id   = auth()->id();
        $note->job_application_id = $request->id;
        $note->save();

        $notes = ApplicantNote::with('user:id,name')
            ->where('job_application_id', $request->id)
            ->orderByDesc('created_at')
            ->get();

        $view = view('admin.job-applications.partials.applicant-notes-list', compact('notes'))->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function update(Request $request, $id)
    {
        abort_if(auth()->user()->role_id !== 1 && auth()->id() !== ApplicantNote::findOrFail($id)->user_id, 403);
        
        $note = ApplicantNote::findOrFail($id);
        $note->note_text = $request->note;
        $note->save();

        $notes = ApplicantNote::with('user:id,name')
            ->where('job_application_id', $note->job_application_id)
            ->orderByDesc('created_at')
            ->get();

        $view = view('admin.job-applications.partials.applicant-notes-list', compact('notes'))->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function destroy($id)
    {
        abort_if(auth()->user()->role_id !== 1, 403);

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
}