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
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_code' => ['required', 'exists:services,code'],
            'transporter_type' => ['nullable', Rule::in(Transporter::getListForBike())],
            'partner_code' => ['nullable', 'exists:partners,code'],
            'partner_satellite' => ['nullable'],

            'sender_name' => ['required', 'string'],
            'sender_phone' => ['required', 'string'],
            'sender_address' => ['required', 'string'],
            'sender_detail_address' => ['nullable', 'string'],
            'sender_latitude' => ['required', 'numeric'],
            'sender_longitude' => ['required', 'numeric'],
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

            'item' => ['required', 'array'],
            '*.moto_type' => ['nullable', Rule::in(MotorBike::getListType())],
            '*.moto_brand' => ['nullable', 'string'],
            '*.moto_cc' => ['nullable', 'numeric'],
            '*.moto_year' => ['nullable', 'numeric'],
            '*.is_insured' => ['nullable', 'boolean'],
            '*.price' => ['required_if:*.is_insured,true', 'numeric'],

            'created_by' => ['nullable', 'exists:customers,id'],
        ];
    }

    public function messages()
    {
        return [
            'transporter_type.in' => 'transporter tidak tersedia, silahkan pilih PICKUP, CDD DOUBLE DAN CDE ENGKEL'
        ];
    }
}
