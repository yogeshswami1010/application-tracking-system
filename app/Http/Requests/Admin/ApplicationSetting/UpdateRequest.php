<?php

namespace App\Http\Requests\Admin\ApplicationSetting;

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
            'legal_term' => 'required',
            'auto_delete_candidates' => 'nullable|boolean',
            'candidate_retention_days' => 'nullable|integer|min:0',
        ];
    }
}
