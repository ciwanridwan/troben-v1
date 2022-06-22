<?php

namespace App\Models\Partners;

use App\Models\Geo\Regency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harbor extends Model
{
    protected $table = 'harbors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'origin_regency_id',
        'origin_name',
        'destination_name',
        'destination_regency_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'origin_regency_id',
        'destination_regency_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Define `belongsTo` relationship with Regency model.
     * @return BelongsTo
     */
    public function origin_regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'origin_regency_id', 'id');
    }

    /**
     * Define `belongsTo` relationship with Regency model.
     *
     * @return BelongsTo
     */
    public function destination_regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'destination_regency_id', 'id');
    }
}
