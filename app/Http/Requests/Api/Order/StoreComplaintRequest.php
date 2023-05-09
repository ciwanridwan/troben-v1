<?php

namespace App\Http\Requests\Api\Order;

use App\Models\Packages\Package;
use Illuminate\Foundation\Http\FormRequest;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class StoreComplaintRequest extends FormRequest
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
            'type' => ['required', 'string'],
            'desc' => ['required', 'string'],
            'photos.*' => ['nullable', 'image:jpg,jpeg,png']
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'Type harus diisi',
            'desc.required' => 'Deskripsi harus diisi',
            'photos.image' => 'Format image harus jpg, jpeg dan png'
        ];
    }
}
