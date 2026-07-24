<?php

namespace App\Http\Requests\Admin\SmsSetting;

use App\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        if (!$this->has('nexmo_status')) {
            $this->request->add(['nexmo_status' => 'deactive']);
        }

        return [
            'sms_provider' => 'required|in:vonage,telnyx',
            'nexmo_key' => [Rule::requiredIf($this->nexmo_status === 'active' && $this->sms_provider === 'vonage'), 'nullable'],
            'nexmo_secret' => [Rule::requiredIf($this->nexmo_status === 'active' && $this->sms_provider === 'vonage'), 'nullable'],
            'nexmo_from' => [Rule::requiredIf($this->nexmo_status === 'active' && $this->sms_provider === 'vonage'), 'nullable', 'between:3,18'],
            'telnyx_api_key' => [Rule::requiredIf($this->nexmo_status === 'active' && $this->sms_provider === 'telnyx'), 'nullable', 'string'],
            'telnyx_from_number' => [Rule::requiredIf($this->nexmo_status === 'active' && $this->sms_provider === 'telnyx'), 'nullable', 'regex:/^\+?1?\d{10}$/'],
            'telnyx_public_key' => [Rule::requiredIf($this->nexmo_status === 'active' && $this->sms_provider === 'telnyx'), 'nullable', 'string'],
        ];
    }
}
