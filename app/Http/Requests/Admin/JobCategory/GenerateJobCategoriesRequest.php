<?php

namespace App\Http\Requests\Admin\JobCategory;

use Illuminate\Foundation\Http\FormRequest;

class GenerateJobCategoriesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'count' => 'required|integer|min:3|max:15',
            'company_description' => 'required|string|max:8000',
        ];
    }
}
