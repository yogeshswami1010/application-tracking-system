<?php

namespace App\Http\Requests;

use App\Job;
use App\Question;
use Illuminate\Support\Arr;

class StoreJobApplication extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $job = null;

        if ($this->filled('job_id')) {
            $job = Job::where('id', $this->job_id)->first();
        }

        $rules = [
            'full_name' => 'required',
            'email' => 'email|required',
            'phone' => 'numeric|required',
            'job_id' => 'nullable|exists:jobs,id',
            'job_job_location_id' => 'nullable|exists:job_job_locations,id',
            'location_id' => 'nullable|integer',
            'resume' => 'nullable|mimes:jpeg,jpg,png,doc,docx,rtf,xls,xlsx,pdf,txt',
            'skills' => 'nullable|string',
        ];

        if ($job) {
            $requiredColumns = $job->required_columns ?: [];
            $sectionVisibility = $job->section_visibility;

            if (!empty($requiredColumns['gender'])) {
                $rules = Arr::add($rules, 'gender', 'required|in:male,female,others');
            }

            if (!empty($requiredColumns['dob'])) {
                $rules = Arr::add($rules, 'dob', 'required|date');
            }

            if (!empty($requiredColumns['country'])) {
                $rules = Arr::add($rules, 'country', 'required|integer|min:1');
                $rules = Arr::add($rules, 'state', 'required|integer|min:1');
                $rules = Arr::add($rules, 'city', 'required');
            }

            if (!is_null($sectionVisibility)) {
                foreach ($sectionVisibility as $key => $section) {
                    if ($section === 'yes') {
                        if ($key === 'profile_image') {
                            $rules = Arr::add($rules, 'photo', 'required|mimes:jpeg,jpg,png');
                        }

                        if ($key === 'resume') {
                            $rules['resume'] = 'required|mimes:jpeg,jpg,png,doc,docx,rtf,xls,xlsx,pdf,txt';
                        }
                    }
                }
            }
        }

        $answers = $this->get('answer');
        if (isset($answers) && !empty($answers)) {
            foreach ($answers as $key => $value) {
                $answer = Question::where('id', $key)->first();

                if ($answer && $answer->required == 'yes') {
                    $rules["answer.{$key}"] = 'required';
                }
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'answer.*.required' => 'This answer field is required.',
            'dob.required' => 'Date of Birth field is required.',
            'country.min' => 'Please select country.',
            'state.min' => 'Please select state.',
            'city.required' => 'Please enter city.',
        ];
    }
}
