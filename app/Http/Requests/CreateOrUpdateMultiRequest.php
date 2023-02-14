<?php

namespace App\Http\Requests;

use App\Models\Packages\Package;
use Illuminate\Foundation\Http\FormRequest;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class CreateOrUpdateMultiRequest extends FormRequest
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
            'package_parent_hash' => ['nullable', new ExistsByHash(Package::class)],
            'package_child_hash' => ['array', 'nullable', new ExistsByHash(Package::class)],
        ];
    }
}
