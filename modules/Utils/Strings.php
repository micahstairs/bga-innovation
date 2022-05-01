<?php

namespace Utils;

class Strings
{
    /**
     * @param string $string1
     * @param string $string2
     * @return bool
     */
    public static function doesStringComeBefore(string $string1, string $string2) : bool
    {
        $strings = [$string1, $string2];
        natsort($strings);
        return array_values($strings)[0] == $string1;
    }
}
