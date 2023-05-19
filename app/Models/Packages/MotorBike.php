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

    protected $table = 'package_bikes';

    protected $fillable =
    [
        'type',
        'merk',
        'cc',
        'years',
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
}
