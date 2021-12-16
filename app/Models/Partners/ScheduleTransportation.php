<?php

namespace App\Models\Partners;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Concerns\Models\CanSearch;
use App\Models\Geo\Regency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Class ScheduleTransportation
 * @package App\Models\Partners
 *
 * @property int $id
 * @property int $partner_id
 * @property int $origin_regency_id
 * @property int $destination_regency_id
 * @property Carbon $departed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read \App\Models\Geo\Regency|null $origin_regency
 * @property-read \App\Models\Geo\Regency|null $destination_regency
 * @property-read Partner partner
 *
 */
class ScheduleTransportation extends Model
{
    use SoftDeletes, CustomSerializeDate, HashableId, HasFactory, CanSearch;


    protected $table = 'schedule_transportations';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'partner_id',
        'origin_regency_id',
        'destination_regency_id',
        'departed_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'partner_id',
        'origin_regency_id',
        'destination_regency_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'departed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    /**
     * Define `belongsTo` relationship with Regency model.
     *
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
