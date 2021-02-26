<?php

namespace App\Http\Resources\Api\Partner\Asset;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Data formatted.
     *
     * @var array
     */
    protected array $data;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        foreach ($this->resource as $users) {
            $this->data[] = $this->formattingRole($users);
        }
        return $this->data;
    }

    /**
     * @param array $users
     *
     * @return array
     */
    protected function formattingRole($users = []): array
    {
        $data = [
            'name' => null,
            'role' => [],
        ];
        foreach ($users as $v) {
            if (is_null($data['name'])) {
                $data['name'] = $v->name;
            }
            $data['role'][] = $v->getOriginal('pivot_role');
        }

        return $data;
    }
}
