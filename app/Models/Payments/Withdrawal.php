<?php

namespace App\Models\Payments;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\Partners\Partner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Veelasky\LaravelHashId\Eloquent\HashableId;

/**
 * Class Withdrawal
 * @package App\Models\Payments
 *
 * @property int $id
 * @property int $partner_id
 * @property int $account_bank_id
 * @property double $first_balance
 * @property double $amount
 * @property double $last_balance
 * @property int $bank_id
 * @property string $account_name
 * @property string $account_number
 * @property string $status
 * @property string $notes
 * @property Carbon                      $created_at
 * @property Carbon                      $updated_at
 *
 * @property-read Partner $partner
 */
class Withdrawal extends Model
{
    use CustomSerializeDate,
        HasFactory,
        HashableId;

    public const STATUS_CREATED = 'created';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CONFIRMED = 'accepted';
    public const STATUS_SUCCESS = 'success';

    protected $table = 'partner_balance_disbursement';

    protected $fillable = [
        'partner_id',
        'account_bank_id',
        'first_balance',
        'amount',
        'last_balance',
        'bank_id',
        'account_name',
        'account_number',
        'status',
        'notes',
        'admin',
    ];

    protected $casts = [
        'first_balance' => 'float',
        'amount' => 'float',
        'last_balance' => 'float'
    ];

    protected $appends = [
        'hash',
    ];

    /**
     * Get all available type on partner balance histories.
     *
     * @return string[]
     */
    public static function getAvailableStatus(): array
    {
        return [
            self::STATUS_CREATED,
            self::STATUS_CONFIRMED,
            self::STATUS_REJECTED,
            self::STATUS_SUCCESS,
        ];
    }

    /**
     * Define `belongsTo` relationship with Partner model.
     *
     * @return BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
    }
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin', 'id');
    }
}
