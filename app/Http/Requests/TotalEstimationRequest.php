<?php

namespace App\Http\Requests;

use App\Models\Packages\Package;
use Illuminate\Foundation\Http\FormRequest;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class TotalEstimationRequest extends FormRequest
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
            'orders' => ['array'],
            'orders.*.package_hash' => ['required', new ExistsByHash(Package::class)],
            'orders.*.destination_id' => ['required', 'numeric', 'exists:geo_sub_districts,id'],
            'orders.*.items' => ['nullable', 'array'],
            'orders.*.items.*.height' => ['nullable', 'numeric'],
            'orders.*.items.*.length' => ['nullable', 'numeric'],
            'orders.*.items.*.width' => ['nullable', 'numeric'],
            'orders.*.items.*.is_insured' => ['nullable', 'boolean'],
            'orders.*.items.*.price' => ['nullable', 'numeric'],
            'orders.*.items.*.weight' => ['nullable', 'numeric'],
            'orders.*.items.*.qty' => ['nullable', 'numeric'],
            'orders.*.items.*.handling' => ['nullable'],
        ];
    }
}
