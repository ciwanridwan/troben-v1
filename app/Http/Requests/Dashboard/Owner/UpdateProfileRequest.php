<?php

namespace App\Http\Requests\Dashboard\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string'],
            'bank_id' => ['nullable', 'numeric'],
            'bank_account_name' => ['nullable', 'string'],
            'bank_account_number' => ['nullable', 'numeric'],
            'avatar' => ['nullable', 'image:png,jpg,jpeg']
        ];
    }
}
