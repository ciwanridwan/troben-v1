<?php

namespace App\Http\Requests;

use App\Casts\Package\Items\Handling;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class CreateOrderRequest extends FormRequest
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
            'package_parent_hash' => ['required', new ExistsByHash(Package::class)],

            // sender
            'customer_id' => ['nullable', 'exists:customers,id'],
            'transporter_type' => ['nullable', Rule::in(Transporter::getAvailableTypes())],
            'service_code' => ['nullable', 'exists:services,code'],
            'sender_name' => ['nullable', 'exists:packages,sender_name'],
            'sender_address' => ['nullable', 'exists:exists:packages,sender_address'],
            'sender_phone' => ['nullable', 'exists:exists:packages,sender_phone'],
            'origin_regency_id' => ['nullable', 'exists:exists:geo_regencies,id'],
            'sender_latitude' => ['nullable', 'string'],
            'sender_longitude' => ['nullable', 'string'],

            // receiver
            'receiver_name' => ['required', 'string'],
            'receiver_address' => ['required', 'string'],
            'receiver_phone' => ['required', 'string'],
            'receiver_way_point' => ['required', 'string'],
            'destination_regency_id' => ['required', 'numeric', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'numeric', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'numeric', 'exists:geo_sub_districts,id'],
            'photos' => ['required', 'array'],

            // items
            'items' => ['nullable', 'array'],
            'items.*.category_item_id' => ['required', 'exists:category_items,id'],
            'items.*.is_glassware' => ['required', 'boolean'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.name' => ['required', 'string'],
            'items.*.price' => ['required_if:is_insured,true', 'numeric'],
            'items.*.desc' => ['string'],
            'items.*.weight' => ['required', 'numeric'],
            'items.*.height' => ['required', 'numeric'],
            'items.*.length' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric'],
            'items.*.is_insured' => ['required', 'boolean'],
            'items.*.handling' => ['nullable', 'array'],
            'items.*.handling.*' => ['string', Rule::in(Handling::getTypes())],
        ];
    }
}
