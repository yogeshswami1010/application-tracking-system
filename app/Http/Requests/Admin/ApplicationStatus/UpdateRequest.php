<?php

namespace App\Http\Requests\Admin\ApplicationStatus;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'status_name' => 'required|unique:application_status,status,'.$this->route('application_status'),
            'status_color' => 'required',
            'status_position' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'status_name' => 'name',
            'status_color' => 'color',
            'status_position' => 'position',
        ];
    }
}
