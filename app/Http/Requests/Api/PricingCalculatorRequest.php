<?php

namespace App\Http\Requests\Api;

use App\Casts\Package\Items\Handling;
use App\Models\Partners\Transporter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PricingCalculatorRequest extends FormRequest
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
            'service_code' => ['required', 'exists:services,code'],
            'partner_code' => ['required', 'exists:partners,code'],
            'transporter_type' => ['required', Rule::in(Transporter::getAvailableTypes())],
            
            // location
            'sender_latitude' => ['required', 'string'],
            'sender_longitude' => ['required', 'string'],
            'destination_sub_district_id.*' => ['required', 'numeric', 'exists:geo_sub_districts,id'],

            // item
            'items' => ['required', 'array'],
            'items.*.is_insured.*' => ['nullable', 'boolean'],
            'items.*.insurance.*' => ['required', 'boolean'],
            'items.*.name.*' => ['required', 'string'],
            'items.*.qty.*' => ['required', 'numeric'],
            'items.*.weight.*' => ['required', 'numeric'],
            'items.*.height.*' => ['required', 'numeric'],
            'items.*.width.*' => ['required', 'numeric'],
            'items.*.length.*' => ['required', 'numeric'],
            'items.*.price.*' => ['required_if:*.is_insured,true', 'numeric'],
            'items.*.handling.*' => ['nullable', 'string'],
            // 'items.*.handling.*.type' => ['string', Rule::in(Handling::getTypes())],
            // 'items.*.handling.*.price' => ['numeric']
        ];
    }
}
