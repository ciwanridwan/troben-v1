<?php

namespace App\Supports;

class Emoji
{
    public const EMOJI_RED_FLAG = '\xF0\x9F\x9A\xA9';
    public const EMOJI_MEGAPHONE = '\xF0\x9F\x93\xA3';

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
