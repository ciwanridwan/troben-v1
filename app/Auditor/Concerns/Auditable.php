<?php

namespace App\Auditor\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    /**
     * Define `morphMany` relationship with Audit model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function audits(): MorphMany
    {
        /** @var \Illuminate\Database\Eloquent\Model $this */
        return $this->morphMany(config('auditor.model'), 'auditable', 'auditable_type', 'auditable_id', $this->getKeyName());
    }
}
