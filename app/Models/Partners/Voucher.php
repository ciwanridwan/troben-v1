<?php

namespace App\Models\Partners;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Concerns\Models\CanSearch;
use App\Models\User;
use Carbon\Carbon;
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
 * @property int $user_id
 * @property string $title
 * @property int $partner_id
 * @property float $discount
 * @property string $code
 * @property bool $is_approved
 *
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property int $is_available
 *
 *
 * @property-read Partner|null $partner
 * @property-read User|null $user
 *
 */
class Voucher extends Model implements AttachableContract
{
    use SoftDeletes, CustomSerializeDate, HashableId, HasFactory, Attachable, CanSearch;

    public const ATTACHMENT_COVER = 'voucher';

    protected $table = 'vouchers';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'title',
        'partner_id',
        'discount',
        'code',
        'start_date',
        'end_date',
        'is_approved',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'is_approved'
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
     * @return BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
