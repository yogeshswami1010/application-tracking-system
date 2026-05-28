<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkill extends CoreRequest
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
        if ($this->routeIs('admin.skills.update')) {
            return [
                'name' => 'required|string|max:255',
                'category_id' => 'required|integer',
            ];
        }

        return [
            'category_id' => 'required|integer',
            'name' => 'required|array|min:1',
            'name.*' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'category_id.required' => __('menu.jobCategories').' '.__('errors.fieldRequired'),
            'name.required' => __('errors.addSkills'),
            'name.*.required' => __('errors.addSkills'),
        ];
    }
}
