<?php

namespace App\Models\Partners;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\Payments\Bank;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BankAccount
 * @package App\Models\Partners
 *
 * @property int $id
 * @property int $user_id
 * @property int bank_id
 * @property string $account_name
 * @property string $account_number
 * @property float $admin_charges
 * @property Carbon                      $created_at
 * @property Carbon                      $updated_at
 */
class BankAccount extends Model
{
    use CustomSerializeDate,
        HasFactory;

    protected $table = 'partner_bank_account';

    protected $fillable = [
        'user_id',
        'bank_id',
        'account_name',
        'account_number',
    ];

    protected $casts = [
    ];

    protected $appends = [
        'id',
    ];

    /**
     * Define `belongsTo` relationship with Partner model.
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function banks(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id');
    }
}
