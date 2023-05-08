<?php

namespace App\Http\Requests\Api\Order;

use App\Models\Packages\Package;
use Illuminate\Foundation\Http\FormRequest;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class StoreRatingReviewRequest extends FormRequest
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
            'package_hash' => ['required', new ExistsByHash(Package::class)],
            'rating' => ['nullable', 'numeric', 'in:1,2,3,4,5'],
            'review' => ['nullable', 'string']
        ];
    }

    public function messages()
    {
        return [
            'rating.in' => 'Rating harus dimulai dari 1 - 5'
        ];
    }
}
