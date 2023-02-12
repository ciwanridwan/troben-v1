<?php

namespace App\Http\Requests;

use App\Models\Packages\Package;
use Illuminate\Foundation\Http\FormRequest;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class EstimationPricesRequest extends FormRequest
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
            'destination_id' => ['required', 'exists:geo_sub_districts,id'],
            'package_hash' => ['required', new ExistsByHash(Package::class)],
            'is_glassware' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*.height' => ['required', 'numeric'],
            'items.*.length' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric'],
            'items.*.weight' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.handling' => ['nullable'],
        ];
    }
}
