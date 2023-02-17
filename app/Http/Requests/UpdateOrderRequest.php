<?php

namespace App\Http\Requests;

use App\Casts\Package\Items\Handling;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // param needs
            'order_type' => ['required', 'in:Single,Multi'],
            'package_hash_child' => ['nullable', 'string'],
            'add_item' => ['nullable', 'boolean'],
            // packages
            'receiver_name' => ['nullable', 'string'],
            'receiver_address' => ['nullable', 'string'],
            'receiver_phone' => ['nullable', 'string'],
            'receiver_way_point' => ['nullable', 'string'],
            'destination_regency_id' => ['nullable', 'numeric', 'exists:geo_regencies,id'],
            'destination_district_id' => ['nullable', 'numeric', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['nullable', 'numeric', 'exists:geo_sub_districts,id'],
            'photos' => ['nullable', 'array'],
        ];
    }
}
