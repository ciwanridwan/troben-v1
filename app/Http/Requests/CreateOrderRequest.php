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
            'orders' => ['array'],
            'orders.*.package_parent_hash' => ['required', new ExistsByHash(Package::class)],

            // sender
            'orders.*.customer_id' => ['nullable', 'exists:customers,id'],
            'orders.*.transporter_type' => ['nullable', Rule::in(Transporter::getAvailableTypes())],
            'orders.*.service_code' => ['nullable', 'exists:services,code'],
            'orders.*.sender_name' => ['nullable', 'exists:packages,sender_name'],
            'orders.*.sender_address' => ['nullable', 'exists:exists:packages,sender_address'],
            'orders.*.sender_phone' => ['nullable', 'exists:exists:packages,sender_phone'],
            'orders.*.origin_regency_id' => ['nullable', 'exists:exists:geo_regencies,id'],
            'orders.*.sender_latitude' => ['nullable', 'string'],
            'orders.*.sender_longitude' => ['nullable', 'string'],

            // receiver
            'orders.*.receiver_name' => ['required', 'string'],
            'orders.*.receiver_address' => ['required', 'string'],
            'orders.*.receiver_phone' => ['required', 'string'],
            'orders.*.receiver_way_point' => ['required', 'string'],
            'orders.*.destination_regency_id' => ['required', 'numeric', 'exists:geo_regencies,id'],
            'orders.*.destination_district_id' => ['required', 'numeric', 'exists:geo_districts,id'],
            'orders.*.destination_sub_district_id' => ['required', 'numeric', 'exists:geo_sub_districts,id'],
            'orders.*.photos' => ['required', 'array'],

            // items
            'orders.*.items' => ['nullable', 'array'],
            'orders.*.items.*.category_item_id' => ['required', 'exists:category_items,id'],
            'orders.*.items.*.is_glassware' => ['required', 'boolean'],
            'orders.*.items.*.qty' => ['required', 'numeric'],
            'orders.*.items.*.name' => ['required', 'string'],
            'orders.*.items.*.price' => ['required_if:is_insured,true', 'numeric'],
            'orders.*.items.*.desc' => ['string'],
            'orders.*.items.*.weight' => ['required', 'numeric'],
            'orders.*.items.*.height' => ['required', 'numeric'],
            'orders.*.items.*.length' => ['required', 'numeric'],
            'orders.*.items.*.width' => ['required', 'numeric'],
            'orders.*.items.*.is_insured' => ['required', 'boolean'],
            'orders.*.items.*.handling' => ['nullable', 'array'],
            'orders.*.items.*.handling.*' => ['string', Rule::in(Handling::getTypes())],
        ];
    }
}
