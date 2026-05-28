<?php

namespace App\Http\Requests\Admin\FooterSetting;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => 'required|unique:footer_settings,name',
            'description' => 'required|string|max:50000',
            'status' => 'required|in:active,inactive',
            'external_url' => 'nullable|string|max:2048',
            'link_target' => 'nullable|in:_self,_blank',
        ];
    }
}
