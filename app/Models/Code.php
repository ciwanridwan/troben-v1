<?php

namespace App\Models;

use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * Class Code
 * @package App\Models
 *
 * @property  int id
 * @property  string content
 * @property-read  null|Delivery|Package|Item codeable
 * @property  Deliverable|null pivot
 */
class Code extends Model
{
    use HasFactory;
    const TYPE_RECEIPT = 'RCP';
    const TYPE_MANIFEST = 'MNF';
    const TYPE_ITEM = 'ITM';


    protected $fillable = [
        'content'
    ];

    protected $hidden = [
        'id',
        'codeable_type',
        'codeable_id',
    ];

    public function codeable(): Relations\MorphTo
    {
        return $this->morphTo();
    }
    public static function generateCodeContent($codeable_type)
    {
        $query = Code::query();

        switch (true) {
            case $codeable_type instanceof Package:
                $pre = self::TYPE_RECEIPT;
                break;
            case $codeable_type instanceof Item:
                $pre = self::TYPE_ITEM;
                break;
            case $codeable_type instanceof Delivery:
                $pre = self::TYPE_MANIFEST;
                break;
        }

        $pre .=  Carbon::now()->format('dmy');
        $last_order = $query->where('content', 'LIKE', $pre . '%')->orderBy('content', 'desc')->first();
        $inc_number = $last_order ? substr($last_order->content, strlen($pre)) : 0;
        $inc_number = (int) $inc_number;
        $inc_number = $last_order ? $inc_number + 1 : $inc_number;

        // assume 100.000/day
        $inc_number = str_pad($inc_number, 5, '0', STR_PAD_LEFT);

        return  $pre . $inc_number;
    }
}
