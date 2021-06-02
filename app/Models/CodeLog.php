<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeLog extends Model
{
    use HasFactory;
    public const TYPE_ERROR = 'error';
    public const TYPE_INFO = 'info';
    public const TYPE_WARNING = 'warning';
    public const TYPE_NEUTRAL = 'neutral';

    public static function getAvailableTypes()
    {
        return [
            self::TYPE_ERROR,
            self::TYPE_INFO,
            self::TYPE_WARNING,
            self::TYPE_NEUTRAL
        ];
    }
}
