<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeLog extends Model
{
    use HasFactory;
    const TYPE_ERROR = 'error';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_NEUTRAL = 'neutral';

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
