<?php

namespace App\Http\Requests\Admin;

use App\Package;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkExperience extends FormRequest
{

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

        return [
            'work_experience' => 'required',
        ];
    }
}
