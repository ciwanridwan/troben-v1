<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotorBike extends Model
{
    use HasFactory;
    public CONST TYPE_MATIC = 'matic';
    public CONST TYPE_KOPLING = 'kopling';
    public CONST TYPE_GIGI = 'gigi';
    
    /**list cc */ 
    public const CC_110 = 110;
    public const CC_125 = 125;
    public const CC_135 = 135;
    public const CC_150 = 150;
    public const CC_250 = 250;
    public const CC_500 = 500;
    public const CC_1000 = 1000;


    protected $table = 'package_bikes';

    protected $fillable =
    [
        'type',
        'merk',
        'cc',
        'years',
        'package_id',
        'package_item_id'
    ];

    protected $hidden = [
        'id',
        'package_id',
        'package_item_id',
        'created_at',
        'updated_at'
    ];


    /**
     * Relation to packages table
     */
    public function packages()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    /**
     * Relation to packages items
     */
    public function packageItems()
    {
        return $this->belongsTo(Package::class, 'package_item_id', 'id');
    }

    /**
     * List type of motobike
     */
    public static function getListType(): array
    {
        return [
        self::TYPE_GIGI,
        self::TYPE_KOPLING,
        self::TYPE_MATIC   
        ];
    }

    public static function getListCc(): array
    {
        return [
            self::CC_110,
            self::CC_125,
            self::CC_135,
            self::CC_150,
            self::CC_250,
            self::CC_500,
            self::CC_1000,
        ];
    }
}
