<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MultiDestination extends Model
{
    use HasFactory;

    protected $table = 'multi_destination_packages';

    protected $fillable = ['parent_id', 'child_id'];
}
