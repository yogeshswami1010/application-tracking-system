<?php

namespace App\Http\Requests\Admin\Skill;

use Illuminate\Foundation\Http\FormRequest;

class AiGenerateSkillsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_ids' => 'required|array|min:1|max:10',
            'category_ids.*' => 'required|integer|exists:job_categories,id',
        ];
    }
}