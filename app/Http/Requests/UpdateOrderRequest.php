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
            'order_type' => ['required', 'in:Single,Multi'],
            'package_hash_child' => ['nullable', 'string'],
            'receiver_name' => ['nullable', 'string'],
            'receiver_address' => ['nullable', 'string'],
            'receiver_phone' => ['nullable', 'string'],
            'receiver_detail_address' => ['nullable', 'string'],
            'dest_regency_id' => ['nullable', 'string', 'exists:geo_regencies,id'],
            'dest_district_id' => ['nullable', 'string', 'exists:geo_districts,id'],
            'dest_sub_district_id' => ['nullable', 'string', 'exists:geo_sub_districts,id'],
            'photos' => ['nullable', 'array'],

            'items' => ['nullable', 'array'],
            'items.*.category_item_id' => ['exists:category_items,id'],
            'items.*.is_glassware' => ['boolean'],
            'items.*.qty' => ['numeric'],
            'items.*.name' => ['string'],
            'items.*.price' => ['numeric'],
            'items.*.desc' => ['string'],
            'items.*.weight' => ['numeric'],
            'items.*.height' => ['numeric'],
            'items.*.length' => ['numeric'],
            'items.*.width' => ['numeric'],
            'items.*.is_insured' => ['boolean'],
            'items.*.handling' => ['nullable', 'array'],
            'items.*.handling.*' => ['string', Rule::in(Handling::getTypes())],
        ];
    }
}
