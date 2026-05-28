<?php

namespace App\Listeners;

use App\JobAlert;
use App\Events\JobAlertEvent;
use App\Notifications\JobAlert as NotificationsJobAlert;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;


class SendJobAlertListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(JobAlertEvent $event)
    {
        
        $jobAlerts = JobAlert::with(['alertLocation', 'alertCategory'])->where('status', '=', 'active')->get();
        
           


        foreach ($jobAlerts as $jobAlert)
        { 
            if(!is_null($jobAlert) && $jobAlert->work_experience_id == $event->job->work_experience_id && $jobAlert->job_type_id == $event->job->job_type_id){
                if(!empty($jobAlert->alertLocation) && !empty($jobAlert->alertCategory)){
                    foreach($jobAlert->alertLocation as $location){
                        if($location->id == $event->job->location_id){
                            
                            Notification::send($jobAlert, new NotificationsJobAlert($event->job));
                        }
                    }
                }
                    
            }

        }

        
    }

}
