<?php

namespace App\Models\Promos;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Concerns\Models\CanSearch;
use App\Models\Geo\Regency;
use Carbon\Carbon;
use Faker\Provider\Text;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jalameta\Attachments\Concerns\Attachable;
use Jalameta\Attachments\Contracts\AttachableContract;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Class Promotion.
 *
 * @property int $id
 * @property string $title
 * @property string $type
 * @property Text $terms_and_conditions
 * @property string $transporter_type
 * @property int $destination_regency_id
 * @property float $min_payment
 * @property float $min_weight
 * @property float $max_weight
 *
 * @property bool $is_active
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property int $is_available
 *
 *
 * @property-read Regency|null $destination_regency
 */
class Promotion extends Model implements AttachableContract
{
    use SoftDeletes, CustomSerializeDate, HashableId, HasFactory, Attachable, CanSearch;

    public const ATTACHMENT_COVER = 'promotion';

    protected $table = 'promotions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'type',
        'terms_and_conditions',
        'transporter_type',
        'destination_regency_id',
        'min_payment',
        'min_weight',
        'max_weight',
        'is_active',
        'start_date',
        'end_date',
        'is_available',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'is_active'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

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
