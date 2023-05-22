<?php

namespace App\Http\Requests\Api\Order;

use App\Casts\Package\Items\Handling;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMultiDestinationRequest extends FormRequest
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
            // general
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_code' => ['required', 'exists:services,code'],
            'transporter_type' => ['required', 'exists:transporters,type'],
            'partner_code' => ['required', 'exists:partners,code'],
            'photos.*.*' => ['nullable', 'image:jpg,jpeg,png'],

            // sender
            'sender_name' => ['required', 'string'],
            'sender_phone' => ['required', 'string'],
            'sender_address' => ['required', 'string'],
            'sender_detail_address' => ['nullable', 'string'],
            'sender_latitude' => ['required', 'string'],
            'sender_longitude' => ['required', 'string'],

            // receiver
            'receiver_name.*' => ['required', 'string'],
            'receiver_phone.*' => ['required', 'string'],
            'receiver_address.*' => ['required', 'string'],
            'receiver_detail_address.*' => ['nullable', 'string'],
            'destination_regency_id.*' => ['required', 'numeric', 'exists:geo_regencies,id'],
            'destination_district_id.*' => ['required', 'numeric', 'exists:geo_districts,id'],
            'destination_sub_district_id.*' => ['required', 'numeric', 'exists:geo_sub_districts,id'],

            // item
            'items' => ['required', 'array'],
            'items.*.is_insured.*' => ['nullable', 'boolean'],
            'items.*.is_glassware.*' => ['nullable', 'boolean'],
            'items.*.name.*' => ['required', 'string'],
            'items.*.qty.*' => ['required', 'numeric'],
            'items.*.desc.*' => ['nullable', 'string'],
            'items.*.weight.*' => ['required', 'numeric'],
            'items.*.height.*' => ['required', 'numeric'],
            'items.*.width.*' => ['required', 'numeric'],
            'items.*.length.*' => ['required', 'numeric'],
            'items.*.price.*' => ['required_if:*.is_insured,true', 'numeric'],
            'items.*.handling.*' => ['nullable', 'array'],
            'items.*.handling.*.*' => ['string', Rule::in(Handling::getTypes())],
            'items.*.category_id.*' => ['required', 'exists:category_items,id']
        ];
    }
}
