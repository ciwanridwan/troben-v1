<?php

namespace App\Models\Payments;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Models\Partners\BankAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Bank.
 *
 * @property int id
 * @property string name
 * @property string code
 * @property Carbon                      $created_at
 * @property Carbon                      $updated_at
 */
class Bank extends Model
{
    use CustomSerializeDate,
        HasFactory;

    protected $table = 'bank';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Define `belongsTo` relationship with Partner model.
     *
     * @return BelongsTo
     */
    public function owner(): HasMany
    {
        return $this->HasMany(BankAccount::class, 'bank_id', 'id');
    }
}
