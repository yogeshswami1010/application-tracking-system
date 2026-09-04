<?php

namespace App\Observers;

use App\Events\CandidateRecordUpdated;
use Illuminate\Database\Eloquent\Model;

class CandidateRealtimeObserver
{
    public function created(Model $candidate): void { $this->broadcast($candidate, 'created'); }
    public function updated(Model $candidate): void { $this->broadcast($candidate, 'updated'); }
    public function deleted(Model $candidate): void { $this->broadcast($candidate, 'deleted'); }
    public function restored(Model $candidate): void { $this->broadcast($candidate, 'restored'); }

    private function broadcast(Model $candidate, string $action): void
    {
        $user = auth()->user();
        $hiddenFields = ['password', 'remember_token', 'parsed_cv_data', 'cv_text'];
        $changedFields = array_values(array_diff(array_keys($candidate->getChanges()), $hiddenFields));
        CandidateRecordUpdated::dispatch(class_basename($candidate), (int) $candidate->getKey(), $action, $changedFields, optional($candidate->updated_at)->toJSON(), $user ? (int) $user->id : null, $user ? (string) $user->name : 'System');
    }
}