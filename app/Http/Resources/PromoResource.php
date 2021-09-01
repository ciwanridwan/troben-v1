<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PromoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Promo $this */

        $data = [
            'hash' => $this->hash,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'author' => $this->author,
            'source' => $this->source,
            'attachment' => $this->image,
            /*'attachment' => $this->attachments()->first()->uri ?? null,*/
            'created_at' => date('Y-m-d h:i:s', strtotime($this->created_at)),
            'updated_at' => date('Y-m-d h:i:s', strtotime($this->updated_at)),
        ];


        return $data;
    }
}
