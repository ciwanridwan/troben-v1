<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryItem extends Model
{
    use HasFactory;

    public const TYPE_BIKE = "Motor";

    protected $table = 'category_items';

    protected $fillable = [
        'name',
        'is_insured',
        'desc',
        'label',
    ];
}
