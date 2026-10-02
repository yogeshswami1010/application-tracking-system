<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpdateProfile extends CoreRequest
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
        $rules = [
            'name' => 'required',
            'email' => 'required|email|regex:/(.*)\./i|unique:users,email,'.$this->route('profile'),
            'image' => 'image|max:2048',
            'password' => 'nullable|min:6',
            'email_signature' => 'nullable|string|max:3000',
            'email_signature_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048|dimensions:max_width=4000,max_height=4000',
            'remove_email_signature_image' => 'nullable|boolean',
            'password_confirmation' => 'nullable|required_with:password|same:password',

        ];

        if ($this->has('mobile')) {
            $rules = Arr::add($rules, 'mobile', 'required|numeric');
            $rules = Arr::add($rules, 'calling_code', 'required');
        }
        
        return $rules;
    }
}
