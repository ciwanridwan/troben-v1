<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingAndReview extends Model
{
    use HasFactory;

    protected $table = 'package_ratings';

    protected $fillable = [
        'package_id',
        'rating',
        'review'
    ];

    /**
     * Relation to packages table
     */
    public function packages(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }
}
