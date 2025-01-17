<?php

namespace App\Models;

use App\Concerns\Controllers\CustomSerializeDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    use HasFactory, CustomSerializeDate;

    protected $table = 'version';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'app',
        'version',
        'description',
        'is_active',
        'code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at ' => 'datetime',
        'updated_at ' => 'datetime',
    ];
}
