<?php

namespace App\Auditor;

use Illuminate\Database\Eloquent\Model;
use App\Concerns\Models\UuidAsPrimaryKey;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditModel extends Model
{
    use UuidAsPrimaryKey;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'audits';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'trails' => '{"before":[],"after":[]}',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trails' => 'array',
    ];

    /**
     * Define `morphTo` relationship with auditable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo('auditable');
    }

    /**
     * Define `morphTo` relationship with performer model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function performer(): MorphTo
    {
        return $this->morphTo('performer');
    }
}
