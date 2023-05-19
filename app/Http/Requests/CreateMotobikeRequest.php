<?php

namespace App\Http\Requests;

use App\Casts\Package\Items\Handling;
use App\Models\Packages\MotorBike;
use App\Models\Partners\Transporter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMotobikeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_code' => ['required', 'exists:services,code'],
            'transporter_type' => ['required', Rule::in(Transporter::getListForBike())],
            'partner_code' => ['required', 'exists:partners,code'],

            'sender_name' => ['required', 'string'],
            'sender_phone' => ['required', 'string'],
            'sender_address' => ['required', 'string'],
            'sender_detail_address' => ['nullable', 'string'],
            'sender_lat' => ['required', 'numeric'],
            'sender_lon' => ['required', 'numeric'],
            'origin_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'origin_district_id' => ['nullable', 'exists:geo_districts,id'],
            'origin_sub_district_id' => ['nullable', 'exists:geo_sub_districts,id'],

            'receiver_name' => ['required', 'string'],
            'receiver_phone' => ['required', 'string'],
            'receiver_address' => ['required', 'string'],
            'receiver_detail_address' => ['nullable', 'string'],
            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id'],

            'photos' => ['required'],
            'photos.*' => ['image:jpg,jpeg,png', 'max:10240'],

            'item' => ['nullable', 'array'],
            'item.*.moto_type' => ['required', Rule::in(MotorBike::getListType())],  
            'item.*.moto_brand' => ['required', 'string'],
            'item.*.moto_cc' => ['required', 'numeric'],
            'item.*.moto_year' => ['required', 'numeric'],
            'item.*.is_insured' => ['nullable', 'boolean'],
            'item.*.price' => ['required_if:*.is_insured,true', 'numeric'],

            'item.*.handling' => ['nullable'],
            'item.*.handling.*' => ['nullable', Rule::in(Handling::TYPE_WOOD)],
            'item.*.height' => [Rule::requiredIf('handling.*', '!=', null), 'numeric'],
            'item.*.length' => [Rule::requiredIf('handling.*', '!=', null), 'numeric'],
            'item.*.width' => [Rule::requiredIf('handling.*', '!=', null), 'numeric'],
            
            'created_by' => ['nullable', 'exists:customers,id'],
        ];
    }
}
