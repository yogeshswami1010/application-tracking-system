<?php

namespace App\Rules;

use App\ApplicationSetting;
use App\JobApplication;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Modules\Recruit\Entities\RecruitJobApplication;
use Modules\Recruit\Entities\RecruitSetting;

class CheckApplication implements Rule
{

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $restriction = ApplicationSetting::select('job_application_limitation')->first();
        
        $jobId = request()->job_id;
        
        $allApplications = JobApplication::where('email', $value)
                            ->where('job_id', $jobId)
                            ->select('email', 'created_at')
                            ->withTrashed()->orderBy('created_at', 'desc')->first();
        
        if ($allApplications && $restriction->job_application_limitation != 0) {
           
            $daysCount = now()->diffInDays($allApplications->created_at);
            
            if ($daysCount >= $restriction->job_application_limitation) {
                return true;
            }

            return false;
        }

            return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The application already exist.';
    }

}
