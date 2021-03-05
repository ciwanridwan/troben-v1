<?php

namespace App\Http\Resources\Api\Partner\Asset;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class UserResource extends JsonResource
{
    /**
     * Data formatted.
     *
     * @var array
     */
    protected array $data = [];

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
            'hash' => null,
            'name' => null,
            'username' => null,
            'email' => null,
            'phone' => null,
            'role' => [],
        ];

        foreach ($users as $user) {
            if (is_null($data['hash'])) {
                foreach (Arr::except($data,'role') as $key => $value) {
                    $data[$key] = $user->{$key};
                }
            }
            $data['role'][] = $user->getOriginal('pivot_role');
        }

        return $data;
    }
}
