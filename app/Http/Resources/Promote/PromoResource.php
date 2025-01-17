<?php

namespace App\Http\Resources\Promote;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Promos\Promo $this */
        $data = [
            'hash' => $this->hash,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'author' => $this->author,
            'portal' => $this->portal,
            'source' => $this->source,
            'attachment' => $this->image,
            /*'attachment' => $this->attachments()->first()->uri ?? null,*/
            'created_at' => date('Y-m-d h:i:s', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d h:i:s', strtotime($this->updated_at)),
        ];

        return $data;
    }
}
