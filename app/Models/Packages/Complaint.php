<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use HasFactory;

    /**
     * Define table
     */
    protected $table = 'package_complaints';

    /**
     * Define a column can fill to table
     */
    protected $fillable = [
        'package_id',
        'type',
        'desc',
        'meta'
    ];

    /**
     * Declare relation to packages table
     */
    public function packages(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }
}
