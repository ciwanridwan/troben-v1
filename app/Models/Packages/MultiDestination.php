<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Veelasky\LaravelHashId\Eloquent\HashableId;

class MultiDestination extends Model
{
    use HasFactory, HashableId;

    protected $table = 'multi_destination_packages';

    protected $fillable = ['parent_id', 'child_id'];

    protected $hidden = ['id', 'created_at', 'updated_at'];

    public function packages(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'child_id');
    }
}
