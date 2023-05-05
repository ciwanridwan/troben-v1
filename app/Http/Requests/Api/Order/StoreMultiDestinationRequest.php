<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Foundation\Http\FormRequest;

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
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_code.*' => ['required', 'exists:services,code'],
            'transporter_type.*' => ['required', 'exists:transporters,type'],
            'sender_name.*' => ['required', 'exist']
        ];
    }
}
