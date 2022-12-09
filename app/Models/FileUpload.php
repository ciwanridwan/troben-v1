<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FileUpload extends Model
{
    use HasFactory;

    public const FILE_TYPE_WAREHOUSE = 'warehouse';

    protected $fillable = [
        'package_id',
        'created_by_id',
        'created_by_type',
        'file',
        'file_type',
        'meta',
    ];

    protected $appends = [
        'file_url'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function getFileUrlAttribute()
    {
        $file = $this->attributes['file'];
        if ($file == null) {
            return null;
        }

        return Storage::disk('s3')->temporaryUrl($file, Carbon::now()->addMinutes(60));
    }
}
