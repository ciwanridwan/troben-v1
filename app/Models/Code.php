<?php

namespace App\Models;

use App\Concerns\Controllers\CustomSerializeDate;
use App\Concerns\Models\CanSearch;
use Carbon\Carbon;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Models\Deliveries\Delivery;
use App\Models\Deliveries\Deliverable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Code.
 *
 * @property  int id
 * @property  string content
 * @property-read  null|Delivery|Package|Item codeable
 * @property  Deliverable|null pivot
 */
class Code extends Model
{
    use HasFactory, CanSearch, CustomSerializeDate;
    public const TYPE_RECEIPT = 'TRB';
    public const TYPE_MANIFEST = 'MNF';
    public const TYPE_ITEM = 'ITM';

    public static $staticMakeVisible;

    protected $table = 'codes';

    protected $search_columns = [
        'content'
    ];

    protected $fillable = [
        'content',
    ];


    protected $hidden = [
        'id',
        'codeable_type',
        'codeable_id',
        'laravel_through_key',
        'pivot'
    ];


    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        if (isset(self::$staticMakeVisible)) {
            $this->makeVisible(self::$staticMakeVisible);
        }
    }


    public function __destruct()
    {
        self::$staticMakeVisible = null;
    }

    public function codeable(): Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function code_logs()
    {
        return $this->morphMany(CodeLogable::class, 'code_logable');
    }

    public function logs()
    {
        $class = CodeLogable::class;
        $class::$staticMakeVisible = ['status', 'showable', 'description'];
        return $this->hasMany(CodeLogable::class, 'code_id', 'id');
    }

    public function scan_receipt_codes()
    {
        return $this->morphToMany(self::class, 'code_logable')->withPivot(['status', 'created_at'])->whereHasMorph('codeable', Package::class);
    }
    public function scan_item_codes()
    {
        return $this->morphToMany(self::class, 'code_logable')->whereHasMorph('codeable', Item::class)->withPivot(['status', 'created_at']);
    }

    public function scanned_in()
    {
        return $this
            ->morphedByMany(self::class, 'code_logable')
            ->withPivot(['status', 'created_at'])
            ->using(CodeLogable::class)
            ->wherePivot('type', CodeLogable::TYPE_SCAN);
    }

    public function scanned_by()
    {
        return $this
            ->morphedByMany(User::class, 'code_logable')
            ->withPivot(['status', 'created_at'])
            ->using(CodeLogable::class)
            ->wherePivot('type', CodeLogable::TYPE_SCAN);
    }


    public static function generateCodeContent($codeable_type)
    {
        $query = self::query();
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

        $pre .= Carbon::now()->format('dmy');
        $last_order = $query->where('content', 'LIKE', $pre.'%')->orderBy('content', 'desc')->first();
        $inc_number = $last_order ? substr($last_order->content, strlen($pre)) : 0;
        $inc_number = (int) $inc_number;
        $inc_number = $last_order ? $inc_number + 1 : $inc_number;

        // assume 100.000/day
        $inc_number = str_pad($inc_number, 5, '0', STR_PAD_LEFT);


        return  $pre.$inc_number;
    }
}
