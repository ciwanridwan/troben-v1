<?php

namespace App\Models\Partners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Partner Model.
 *
 * @property int $id
 * @property string $name
 * @property string $contact_email
 * @property string $contact_phone
 * @property string $address
 * @property string $geo_location
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \App\Models\Partners\Warehouse[]|\Illuminate\Database\Eloquent\Collection $warehouses
 * @property-read \App\Models\Partners\Transporter[]|\Illuminate\Database\Eloquent\Collection $transporters
 */
class Partner extends Model
{
    use SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'partners';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'contact_email',
        'contact_phone',
        'address',
        'geo_location',
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
     * Define `hasMany` relationship with Warehouse model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'partner_id', 'id');
    }

    /**
     * Define `hasMany` relationship with Transporter model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transporters(): HasMany
    {
        return $this->hasMany(Transporter::class, 'partner_id', 'id');
    }
}
