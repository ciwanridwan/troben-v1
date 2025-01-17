<?php

namespace App\Http\Requests\Dashboard\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
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
            'old_password' => ['required'],
            'new_password' => ['required', 'confirmed', Password::min(8)]
        ];
    }

    public function messages()
    {
       return [
        'new_password.confirmed' => 'Password Confirmation Not Match With New Password'
       ];
    }
}
