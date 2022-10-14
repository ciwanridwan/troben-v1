<?php

namespace App\Models\Payments;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\Partners\Balance\DisbursmentHistory;
use App\Models\Partners\Partner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Support\Facades\Storage;


/**
 * Class Withdrawal.
 *
 * @property int $id
 * @property int $partner_id
 * @property int $account_bank_id
 * @property float $first_balance
 * @property float $amount
 * @property float $last_balance
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

    // public const STATUS_CREATED = 'created';
    // public const STATUS_REJECTED = 'rejected';
    // public const STATUS_CONFIRMED = 'accepted';
    // public const STATUS_SUCCESS = 'success';

    // Todo New Status
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_TRANSFERRED = 'transferred';
    // End Todo

    /** Set unique code of transaction */
    public const TRANSACTION_CODE = 'WTD';

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
        'attachment_transfer',
        'status',
        'notes',
        'charge_admin',
        'fee_charge_admin',
        'expired_at',
        'trx_code' // transaction code WTD2022101401
    ];

    protected $casts = [
        'first_balance' => 'float',
        'amount' => 'float',
        'last_balance' => 'float'
    ];

    protected $appends = [
        'hash',
        'attachment_transfer_url'
    ];

    /**
     * Get all available type on partner balance histories.
     *
     * @return string[]
     */
    public function getAttachmentTransferUrlAttribute()
    {
        $attachment = $this->attachment_transfer;
        if ($attachment == null) {
            return null;
        }
        return Storage::disk('s3')->temporaryUrl('attachment_transfer/'.$attachment, Carbon::now()->addMinutes(60));
    }

    public static function getAvailableStatus(): array
    {
        return [
            // self::STATUS_CREATED,
            // self::STATUS_CONFIRMED,
            // self::STATUS_REJECTED,
            // self::STATUS_SUCCESS,
            self::STATUS_TRANSFERRED,
            self::STATUS_REQUESTED,
            self::STATUS_APPROVED,
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

    public function disbursmentHistories(): BelongsTo
    {
        return $this->belongsTo(DisbursmentHistory::class, 'disbursment_id', 'id');
    }

}
