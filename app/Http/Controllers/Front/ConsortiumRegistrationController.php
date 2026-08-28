<?php

namespace App\Http\Controllers\Front;

use App\ConsortiumRegistration;
use App\Helper\Files;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsortiumRegistrationController extends Controller
{
    public function create()
    {
        return view('front.consortium-registration');
    }

    public function store(Request $request)
    {
        abort_if($request->filled('website'), 422);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'gender' => ['nullable', 'string', 'max:40'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'street_address' => ['nullable', 'string', 'max:190'],
            'city' => ['required', 'string', 'max:100'],
            'eligible_to_work_canada' => ['required', 'boolean'],
            'status_in_canada' => ['nullable', 'string', 'max:100'],
            'preferred_job_type' => ['nullable', 'string', 'max:100'],
            'commute_mode' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'numeric', 'min:0', 'max:80'],
            'industry_expertise' => ['nullable', 'string', 'max:190'],
            'available_weekends' => ['required', 'boolean'],
            'available_night_shifts' => ['required', 'boolean'],
            'referral_source' => ['nullable', 'string', 'max:100'],
            'additional_information' => ['nullable', 'string', 'max:5000'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx,rtf', 'max:10240'],
            'information_certified' => ['accepted'],
            'agreement_accepted' => ['accepted'],
            'sms_consent' => ['nullable', 'accepted'],
        ]);

        unset($data['resume']);
        $data['information_certified'] = true;
        $data['agreement_accepted'] = true;
        $data['sms_consent'] = $request->boolean('sms_consent');

        if ($request->hasFile('resume')) {
            $data['resume_original_name'] = $request->file('resume')->getClientOriginalName();
            $data['resume_file'] = Files::uploadLocalOrS3($request->file('resume'), 'registration-resumes');
        }

        ConsortiumRegistration::create($data);

        return back()->with('registration_success', true);
    }
}