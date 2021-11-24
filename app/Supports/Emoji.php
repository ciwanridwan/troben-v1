<?php

namespace App\Supports;

use App\Models\Code;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Supports\Translates\Delivery as TranslatesDelivery;
use App\Supports\Translates\Package as TranslatesPackage;
use Illuminate\Database\Eloquent\Model;

class Emoji
{
    public const EMOJI_RED_FLAG = '\xF0\x9F\x9A\xA9';

    /**
     * @param string $utf8Byte
     * @return array|string|string[]|null
     */
    public static function generateEmoji(string $utf8Byte)
    {
        $pattern = '@\\\x([0-9a-fA-F]{2})@x';
        return preg_replace_callback(
            $pattern,
            function ($captures) {
                return chr(hexdec($captures[1]));
            },
            $utf8Byte
        );
    }
}
