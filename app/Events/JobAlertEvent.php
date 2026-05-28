<?php

namespace App\Events;

use App\Job;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobAlertEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $job;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */

    public function __construct(Job $job)
    {
        
        $this->job = $job;
        //dd($this->job->slug);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [];
    }

}
