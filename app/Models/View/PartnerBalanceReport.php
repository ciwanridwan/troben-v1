<?php

namespace App\Models\View;

use App\Models\Geo\Regency;
use App\Models\Packages\Package;
use App\Models\Partners\Balance\History;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * View Payment Report Model.
 *
 * @property int                        $id
 * @property int                        $partner_id
 * @property string                     $partner_code
 * @property string                     $partner_name
 * @property string                     $partner_type
 * @property int                        $partner_geo_regency_id
 * @property string                     $partner_geo_regency
 * @property int|null                   $package_id
 * @property string                     $package_code
 * @property \Carbon\Carbon             $package_created_at
 * @property int|null                   $disbursement_id
 * @property float                      $balance
 * @property string                     $type
 * @property string                     $description
 * @property int                        $created_at_day
 * @property int                        $created_at_month
 * @property int                        $created_at_year
 * @property \Carbon\Carbon             $history_created_at
 *
 * @property-read Partner               $partner
 * @property-read Package|null          $package
 * @property-read Regency|null          $regency
 * @property-read Collection|History[]  $balanceHistories
 */
class PartnerBalanceReport extends Model
{
    protected $table = 'view_partner_balance_report';

    protected $casts = [
        'balance' => 'int'
    ];

    /**
     * Define relation with partner.
     *
     * @return Relations\BelongsTo
     */
    public function partner(): Relations\BelongsTo
    {
        return $this->belongsTo(Partner::class,'partner_id','id');
    }

    /**
     * Define relation with package.
     *
     * @return Relations\BelongsTo
     */
    public function package(): Relations\BelongsTo
    {
        return $this->belongsTo(Package::class,'package_id','id');
    }

    /**
     * Define relation with geo regency.
     *
     * @return Relations\BelongsTo
     */
    public function regency(): Relations\BelongsTo
    {
        return $this->belongsTo(Regency::class,'partner_geo_regency_id','id');
    }

    /**
     * Define relation with partner balance history.
     * @return Relations\HasMany
     */
    public function balanceHistories(): Relations\HasMany
    {
        return $this->hasMany(History::class,'package_id','package_id');
    }
}
