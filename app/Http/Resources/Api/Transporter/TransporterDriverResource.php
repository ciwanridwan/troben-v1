<?php

namespace App\Http\Resources\Api\Transporter;

use Illuminate\Http\Resources\Json\JsonResource;

class TransporterDriverResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);

        $data['transporter'] = $data['userable'];
        unset($data['userable']);

        return $data;
    }
}
