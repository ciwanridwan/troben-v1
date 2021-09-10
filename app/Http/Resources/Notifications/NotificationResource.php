<?php

namespace App\Http\Resources\Notifications;

use App\Models\Notifications\Notification;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Notification $this */
        return [
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'notify_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
