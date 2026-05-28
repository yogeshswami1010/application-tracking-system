<?php

namespace App\Http\Requests\Onboard;

use App\Http\Requests\CoreRequest;

class UpdateStatus extends CoreRequest
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
        return [
            'cancel_reason'  => 'required',
            
        ];
    }
}
