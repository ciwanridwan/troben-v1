<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'receiver_name' => ['nullable', 'string'],
            'receiver_address' => ['nullable', 'string'],
            'receiver_phone' => ['nullable', 'string'],
            'receiver_detail_address' => ['nullable', 'string'],
            'dest_regency_id' => ['nullable', 'string', 'exists:geo_regencies,id'],
            'dest_district_id' => ['nullable', 'string', 'exists:geo_districts,id'],
            'dest_sub_district_id' => ['nullable', 'string', 'exists:geo_sub_districts,id'],
            'photo' => ['nullable', 'array', 'image:png,jpg,jpeg'],
            'items' => ['nullable', 'array'],
        ];
    }
}
