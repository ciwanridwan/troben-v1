<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jalameta\Attachments\Concerns\Attachable;
use Jalameta\Attachments\Contracts\AttachableContract;

class Promo extends Model implements AttachableContract
{
    use HasFactory, Attachable;

    public const ATTACHMENT_VIDEO = 'promo_video';

    protected $table = 'promo';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'is_active',
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
