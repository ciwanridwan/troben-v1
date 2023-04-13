<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransporterImage extends Model
{
    use HasFactory;

    protected $table = 'transporter_images';

    protected $fillable = [
        'path',
        'transporter_id'
    ];

    protected $hidden = [
        "id",
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'url'
    ];


    public function getUrlAttribute(): string
    {
        return generateUrl($this->path);
    }
}
