<?php

namespace App\Casts\Notification;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Data implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return false|mixed|string
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return json_encode($value);
    }
}
