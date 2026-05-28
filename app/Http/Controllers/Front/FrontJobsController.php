<?php

namespace App\Http\Controllers\Front;

use App\Job;
use App\User;
use App\Skill;
use App\Company;
use App\JobType;
use App\JobAlert;
use Carbon\Carbon;
use App\JobCategory;
use App\JobLocation;
use App\Helper\Files;
use App\Helper\Reply;
use App\FooterSetting;
use App\JobApplication;
use App\WorkExperience;
use App\LanguageSetting;
use App\LinkedInSetting;
use App\ApplicationSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\JobApplicationAnswer;
use App\Mail\ReceivedApplication;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreJobAlert;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Notifications\NewJobApplication;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\FrontJobApplication;
// use App\Http\Requests\StoreJobAlert;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Front\FrontBaseController;
use App\JobJobLocation;

class FrontJobsController extends FrontBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('modules.front.jobOpenings');

        $linkedinSetting = LinkedInSetting::where('status', 'enable')->first();
        $this->linkedinGlobal = LinkedInSetting::first();
        $this->perPage = 6;

        if ($linkedinSetting)
        {
            Config::set('services.linkedin.client_id', $linkedinSetting->client_id);
            Config::set('services.linkedin.client_secret', $linkedinSetting->client_secret);
            Config::set('services.linkedin.redirect', $linkedinSetting->callback_url);
        }
    }

    public function jobOpenings()
    {  
        $metaDetails = json_decode($this->global->meta_details);

        $this->metaTitle = "";
        $this->metaDescription = "";

        if(isset($metaDetails->description))
        {
            $this->metaDescription = $metaDetails->description;
        }

        if(isset($metaDetails->title))
        {
            $this->metaTitle = $metaDetails->title;
        }

        $this->metaImage = $this->global->logo_url;
        $this->jobs = JobJobLocation::all()->take($this->perPage);
        $this->jobCount = JobJobLocation::count();
        $this->locations = JobLocation::all();
        $this->categories = JobCategory::all();
        $this->skills = Skill::all();
        $this->companies = Company::all();

        $jobLocations = JobJobLocation::select('job_job_locations.id as id', 'jobs.id as job_id', 'job_locations.id as location_id')->with(['job', 'location'])
        ->join('job_locations', function ($join) {
            $join->on('job_locations.id', '=', 'job_job_locations.location_id');
        })
        ->join('jobs', 'jobs.id', 'job_job_locations.job_id', function($query){

        })
        ->where('jobs.status', 'active')
            ->where('jobs.start_date', '<=', Carbon::now()->format('Y-m-d'))
            ->where(function($query){
                $query->where('jobs.end_date', '>=', Carbon::now()->format('Y-m-d'));
                $query->orWhereNull('jobs.end_date');
            })
        ->get();
        $this->jobCount = $jobLocations->count();
        $this->jobLocations = $jobLocations->take($this->perPage);
        // dd($this->perPage);

        // dd($this->jobLocations);
        
        return view('front.job-openings', $this->data);
    }

    function moreData(Request $request){
        
        if($request->ajax()){
            $this->locations = JobLocation::all();
            $this->categories = JobCategory::all();
            $this->jobCount = JobJobLocation::count();
                $job = $this->data($request);
                $totalCurrentData = $request->totalCurrentData;

                $jobLocations = JobJobLocation::select('job_job_locations.id as id', 'jobs.id as job_id', 'job_locations.id as location_id')->with(['job', 'location'])
                ->join('job_locations', function ($join) {
                    $join->on('job_locations.id', '=', 'job_job_locations.location_id');
                })
                ->join('jobs', 'jobs.id', 'job_job_locations.job_id', function($query){
        
                })
                ->where('jobs.status', 'active')
                    ->where('jobs.start_date', '<=', Carbon::now()->format('Y-m-d'))
                    ->where(function($query){
                        $query->where('jobs.end_date', '>=', Carbon::now()->format('Y-m-d'));
                        $query->orWhereNull('jobs.end_date');
                    });
                    

                    if($request->location_id != '' && $request->location_id != 'all' ) {
                        $jobLocations = $jobLocations->where('job_job_locations.location_id',$request->location_id );
                    }
            
                    if($request->category != '' && $request->category != 'all' ) {
                        $jobLocations = $jobLocations->where('jobs.category_id', '=', $request->category);    
                    }
            
                    if($request->company != '' && $request->company != 'all' ) {
                        $jobLocations = $jobLocations->where('jobs.company_id', '=', $request->company); 
                    }
            
                    if($request->skill != '' && $request->skill != 'all' ) {
                        $jobLocations = $jobLocations->leftJoin('job_skills', 'job_skills.job_id', 'jobs.id') 
                        ->where('job_skills.skill_id', '=', $request->skill); 
                    }
                    
                $this->jobLocationCount = $jobLocations->count();
                $this->jobLocations = $jobLocations->get()->skip($totalCurrentData)->take($this->perPage);        
                $this->job_current_count = $totalCurrentData + $this->perPage;
                $this->hideButton = 'no';
                if($this->job_current_count > $this->jobLocationCount){
                    $this->hideButton = 'yes';
                }

                $view = view('front.more_data', $this->data)->render();
                return Reply::dataOnly(['status' => 'success', 'view' => $view,'data' => $this->data]);

        }
    }
    
    function searchJob(Request $request)
    {
       $this->locations = JobLocation::all();
       $this->categories = JobCategory::all();
       $this->skills = Skill::all();
       $this->companies = Company::all();
       $totalCurrentData = $request->totalCurrentData;
       $this->jobLocationCount = '';

        $jobLocations = JobJobLocation::select('job_job_locations.id as id', 'jobs.id as job_id', 'job_locations.id as location_id')->with(['job', 'location'])
        ->join('job_locations', function ($join) {
            $join->on('job_locations.id', '=', 'job_job_locations.location_id');
        })
        ->join('jobs', 'jobs.id', 'job_job_locations.job_id', function($query){

        })
        ->where('jobs.status', 'active')
            ->where('jobs.start_date', '<=', Carbon::now()->format('Y-m-d'))
            ->where(function($query){
                $query->where('jobs.end_date', '>=', Carbon::now()->format('Y-m-d'));
                $query->orWhereNull('jobs.end_date');
            });

        if($request->location_id != '' && $request->location_id != 'all' ) {
            $jobLocations = $jobLocations->where('job_job_locations.location_id',$request->location_id );
        }

        if($request->category != '' && $request->category != 'all' ) {
            $jobLocations = $jobLocations->where('jobs.category_id', '=', $request->category);    
        }

        if($request->company != '' && $request->company != 'all' ) {
            $jobLocations = $jobLocations->where('jobs.company_id', '=', $request->company); 
        }

        if($request->skill != '' && $request->skill != 'all' ) {
            $jobLocations = $jobLocations->leftJoin('job_skills', 'job_skills.job_id', 'jobs.id') 
            ->where('job_skills.skill_id', '=', $request->skill); 
        }

        $this->jobLocationCount = $jobLocations->count();
        $this->jobLocations = $jobLocations->get()->skip($totalCurrentData)->take($this->perPage);
        $this->job_current_count = $totalCurrentData + $this->perPage;
        
        $this->hideButton = 'no';
        if($this->job_current_count > $this->jobLocationCount){
            $this->hideButton = 'yes';
        }

        $view = view('front.more_data', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view,'data' => $this->data]);
    }

    function data($request)
    {

        $jobs = Job::where('status', 'active')
            ->where('start_date', '<=', Carbon::now()->format('Y-m-d'))
            ->where('end_date', '>=', Carbon::now()->format('Y-m-d'));


        if ($request->category !== null && $request->category != 'all' ) {
            $jobs= $jobs->where('category_id',$request->category);
        }

        if ($request->location_id !== null && $request->location_id != 'all' ) {
            $jobs= $jobs->where('location_id', $request->location_id);
        }

        if ($request->skill !== null && $request->skill != 'all' ) {
            $jobs= $jobs->join('job_skills','job_skills.job_id', 'jobs.id')
            ->where('job_skills.skill_id', $request->skill);
        }
        
        if ($request->company !== null && $request->company != 'all' ) {
            $jobs= $jobs->where('company_id', $request->company);
        }

        return $jobs->get();
    }
    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function customPage($slug)
    {

        $this->customPage = FooterSetting::where('slug', $slug)->where('status', 'active')->first();

        if(is_null($this->customPage)){ abort(404); }

        $this->pageTitle = ucfirst($this->customPage->name);
        $this->metaTitle = $this->customPage->name;
        $this->metaDescription = $this->customPage->description; 
        return view('front.custom-page', $this->data);
    }

    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function jobDetail($slug, $location = null)
    {
        $this->job = Job::with(['workExperience', 'jobType'])->where('slug', $slug)
            ->whereDate('start_date', '<=', Carbon::now())  
            ->whereDate('end_date', '>=', Carbon::now())
            ->where('status', 'active')
            ->firstOrFail();
            Session::put('lastPageUrl', $slug);
        

        $locationId =  JobJobLocation::where('job_id', $this->job->id)->where('location_id', $location)->first() ? JobJobLocation::where('job_id', $this->job->id)->where('location_id', $location)->first()->id : null;
        $this->location = JobJobLocation::withoutGlobalScope('company')->find($locationId);
        $this->locations =  ($this->location && $this->location->location_id) ? JobLocation::withoutGlobalScope('company')->where('id', $this->location->location_id)->first() : null;
        $this->linkedinGlobal = LinkedInSetting::first();

        Session::put('slug', $slug);

        $this->pageTitle = $this->job->title . ' - ' . $this->companyName;
        $this->metaTitle = "";
        $this->metaDescription = "";

         if(isset($this->job->meta_details['title'])){
            $this->metaTitle = $this->job->meta_details['title'];
         }

         if(isset($this->job->meta_details['description'])){
            $this->metaDescription = $this->job->meta_details['description'];
         }

         $this->metaImage = $this->job->company->logo_url;
        $this->pageUrl = request()->url();
        return view('front.job-detail', $this->data);
    }

    /**
     * @param $provider
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function callback($provider, Request $request)
    {
        if ($request->error) {
            $this->errorCode = $request->error;
            $this->error = $request->error_description;
            return view('errors.linkedin', $this->data);
        }
        
        $this->user = Socialite::driver($provider)->user();
        $this->lastPageUrl = Session::get('lastPageUrl');
        Session::put('accessToken', $this->user->token);
        Session::put('expiresIn', $this->user->expiresIn);
        return redirect()->route('jobs.jobApply', $this->lastPageUrl);
    }

    /**
     * @param $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function jobApply($slug, $location=null)
    {
        $this->job = Job::where('slug', $slug)->where('status', 'active')->first();
        
        abort_if(!$this->job, 404);

        $this->metaTitle = "";
        $this->metaDescription = "";

        if(isset($this->job->meta_details['title'])){
            $this->metaTitle = $this->job->meta_details['title'];
        }
        if(isset($this->job->meta_details['title'])){
            $this->metaDescription = $this->job->meta_details['description'];
        }

        $this->metaImage = $this->job->company->logo_url;
        $this->location = JobLocation::where('id', $location)->first();   
        if(is_null($this->location)) {
            $this->location = JobLocation::withoutGlobalScope('company')->where('id', $location)->first();  
        }
        $this->accessToken = Session::get('accessToken');
        if ($this->accessToken)
        {
            $this->user = Socialite::driver('linkedin')->userFromToken($this->accessToken);
        }
        else{
            $this->user =[];
        }

        $this->jobQuestion = $this->job->questions;
        
        $this->applicationSetting = ApplicationSetting::first();
        $this->pageTitle = $this->job->title . ' - ' . $this->companyName;


        return view('front.job-apply', $this->data);
    }

    /**
     * @param FrontJobApplication $request
     * @return mixed
     */
    public function saveApplication(FrontJobApplication $request)
    {
        $jobLocationData = JobJobLocation::where('job_id', $request->job_id)->first();

        $jobApplication = new JobApplication();
        $jobApplication->full_name = $request->full_name;
        $jobApplication->job_id = $request->job_id;
        $jobLocationData = JobJobLocation::where('job_id', $request->job_id)->first();

        if ($jobLocationData) {
            $jobApplication->location_id = $jobLocationData->location_id;
        }
        $jobApplication->status_id = 1; //applied status id
        $jobApplication->email = $request->email;
        $jobApplication->phone = $request->phone;

        if ($request->has('gender')) {
            $jobApplication->gender = $request->gender;
        }

        if ($request->has('address')) {
            $jobApplication->address = $request->address;
        }

        if ($request->has('dob')) {
            $jobApplication->dob = $request->dob;
        }

        if ($request->has('country')) {
            $countriesArray = json_decode(file_get_contents(public_path('country-state-city/countries.json')), true)['countries'];
            $statesArray = json_decode(file_get_contents(public_path('country-state-city/states.json')), true)['states'];

            $jobApplication->country = $this->getName($countriesArray, $request->country);
            $jobApplication->state = $this->getName($statesArray, $request->state);
            $jobApplication->city = $request->city;
            $jobApplication->zip_code = $request->zip_code;
        }

        $jobApplication->cover_letter = $request->cover_letter;
        $jobApplication->column_priority = 0;

        if ($request->hasFile('photo')) {
            $jobApplication->photo = Files::uploadLocalOrS3($request->photo, 'candidate-photos');
        }
        
        $jobApplication->save();

        if ($request->hasFile('resume')) {
            $hashname = Files::uploadLocalOrS3($request->resume, 'documents/'.$jobApplication->id, null, null, false);
            $jobApplication->documents()->create([
                'name' => 'Resume',
                'hashname' => $hashname
            ]);
        }

        $linkedin = false;

        if($request->linkedinPhoto)
        {
            $contents = file_get_contents($request->linkedinPhoto);
            $getfilename =  str_replace(' ', '_', $request->full_name);
            $filename = $jobApplication->id.$getfilename.'.png';
            Storage::put('candidate-photos/'.$filename, $contents);
            $jobApplication = JobApplication::find($jobApplication->id);
            $jobApplication->photo = $filename;
            $jobApplication->save();
        }
        
        $users = User::allAdmins();
        $global = $this->global;
        if (!empty($request->answer)) {
            foreach ($request->answer as $key => $value) {
                $answer = new JobApplicationAnswer();
                $answer->job_application_id = $jobApplication->id;
                $answer->job_id = $jobApplication->job_id;
                $answer->question_id = $key;
                if($request->hasFile('answer.' . $key)){
                    $answer->file = Files::uploadLocalOrS3($value,'documents');
                }else{
                    $answer->answer = $value;
                }
                $answer->save();
            }
        }
        if($request->has('apply_type')){
            $linkedin = true;
        }
       // Notification::send($users, new NewJobApplication($jobApplication, $linkedin));
        //Mail::send(new ReceivedApplication($jobApplication, $global));
        try {
            Notification::send($users, new NewJobApplication($jobApplication, $linkedin));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
        
        try {
            Mail::send(new ReceivedApplication($jobApplication, $global));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
        return Reply::dataOnly(['status' => 'success', 'msg' => __('modules.front.applySuccessMsg')]);
    }

    public function fetchCountryState(Request $request)
    {
        $responseArr = [];

        $response = [
            "status" => "success", 
            "tp" => 1,
            "msg" => "Countries fetched successfully."
        ];

        switch ($request->type) {
            case 'getCountries':
                $countriesArray = json_decode(file_get_contents(public_path('country-state-city/countries.json')), true)['countries'];

                foreach ($countriesArray as $country) {
                    $responseArr = Arr::add($responseArr, $country['id'], $country['name']);
                }
            break;
            case 'getStates':
                $statesArray = json_decode(file_get_contents(public_path('country-state-city/states.json')), true)['states'];
                $countryId = $request->countryId;

                $filteredStates = array_filter($statesArray, function ($value) use ($countryId) {
                    return $value['country_id'] == $countryId;
                });

                foreach ($filteredStates as $state) {
                    $responseArr = Arr::add($responseArr, $state['id'], $state['name']);
                }
            break;
        }
        $response = Arr::add($response, "result", $responseArr);                

        return response()->json($response);
    }

    public function getName($arr, $id)
    {
        $result = array_filter($arr, function ($value) use ($id) {
            return $value['id'] == $id;
        });
        return current($result)['name'];
    }
    
    public function changeLanguage($code)
    {
        $language = LanguageSetting::where('language_code', $code)->first();

        if (!$language) {
            return Reply::error('invalid language code');
        }

        return response(Reply::success(__('messages.languageChangedSuccessfully')))->cookie('language_code', $code);
    }

    public function jobAlert()
    {
        $this->jobCategorys = JobCategory::all();
        $this->locations = JobLocation::all();
        $this->workExperiences = WorkExperience::all();
        $this->jobTypes = JobType::all();
        return view('front.job-alert', $this->data);
       
    }

    public function saveJobAlert(StoreJobAlert $request)
    {
        $jobAlert = new JobAlert();
        $jobAlert->email = $request->email;
        $jobAlert->work_experience_id = $request->workExperience;
        $jobAlert->job_type_id = $request->jobType;
        $jobAlert->status = "active";
        $jobAlert->hash = str_random(16);
        $jobAlert->save();

        $jobAlert->alertCategory()->sync($request->jobCategory);
        $jobAlert->alertLocation()->sync($request->location);

        return Reply::success(__('messages.jobAlert'));
        

    }

    public function disableJobAlert()
    {
           
        JobAlert::where('id', request()->id)->update(['status' => 'inactive']);
        return Reply::redirect(route('jobs.jobOpenings'), __('messages.disableJobAlert'));
    }

}
