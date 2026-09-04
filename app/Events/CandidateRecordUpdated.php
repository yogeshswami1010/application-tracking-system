<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CandidateRecordUpdated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $candidateType, public int $candidateId, public string $action, public array $changedFields, public ?string $version, public ?int $actorId, public string $actorName) {}

    public function broadcastOn(): array { return [new PrivateChannel('ats.updates')]; }
    public function broadcastAs(): string { return 'candidate.updated'; }
    public function broadcastWith(): array
    {
        return ['candidate_type' => $this->candidateType, 'candidate_id' => $this->candidateId, 'action' => $this->action, 'changed_fields' => $this->changedFields, 'version' => $this->version, 'actor_id' => $this->actorId, 'actor_name' => $this->actorName];
    }
}