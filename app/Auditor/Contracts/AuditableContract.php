<?php

namespace App\Auditor\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface AuditableContract
{
    /**
     * Get the attributes that were changed.
     *
     * @return array
     */
    public function getChanges(): array;

    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public function getHidden(): array;

    /**
     * Get the model's raw original attribute values.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed|array
     */
    public function getRawOriginal($key = null, $default = null): array;

    /**
     * Get a subset of the model's attributes.
     *
     * @param  array|mixed  $attributes
     * @return array
     */
    public function only($attributes): array;

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey();

    /**
     * Define `morphMany` relationship with Audit model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function audits(): MorphMany;

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass();
}
