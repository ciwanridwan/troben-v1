<?php

namespace App\Models\Partners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transporter model.
 *
 * @property int $id
 * @property int $partner_id
 * @property string $name
 * @property string $registration_number
 * @property string $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read \App\Models\Partners\Partner $partner
 */
class Transporter extends Model
{
    use SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transporters';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'partner_id',
        'name',
        'registration_number',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Define `belongsTo` relationship with partner model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
    }
}