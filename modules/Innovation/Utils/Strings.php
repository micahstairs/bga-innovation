<?php

namespace Innovation\Utils;

class Strings
{
    /**
     * Does the first string come alphanumerically before the second?
     *
     * @param string $string1
     * @param string $string2
     * @return bool
     */
    public static function doesStringComeBefore(string $string1, string $string2) : bool
    {
        $string1 = strtolower(self::stripPunctuation(self::stripAccents($string1)));
        $string2 = strtolower(self::stripPunctuation(self::stripAccents($string2)));
        $strings = [$string1, $string2];
        natsort($strings);
        return array_values($strings)[0] == $string1;
    }

    /**
     * Strip all accents from a string
     *
     * @param string $str
     * @return string
     */
    public static function stripAccents(string $str): string
    {
        $str = htmlentities($str, ENT_COMPAT, 'UTF-8');
        $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|ring|slash);/','$1',$str);
        return html_entity_decode($str);
    }

    public static function stripPunctuation(string $str): string
    {
        return preg_replace('/[^a-z\d]+/i', '', $str);
    }
}
