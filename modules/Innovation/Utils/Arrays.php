<?php

namespace Innovation\Utils;

class Arrays
{

    /** Flatten an array (one level) **/
    public static function flatten($array): array
    {
        $result = array();
        foreach ($array as $key => $subarray) {
            $result = array_merge($result, $subarray);
        }
        return $result;
    }

    /**
     * @param array $array
     * @return int
     */
    public static function encode(array $array): int
    {
        $encodedValue = 0;
        foreach ($array as $value) {
            $encodedValue += pow(2, $value);
        }
        return $encodedValue;
    }

    /**
     * @param int $encodedValue
     * @return array
     */
    public static function decode(int $encodedValue): array
    {
        $array = [];
        $value = 0;
        while ($encodedValue > 0) {
            if ($encodedValue % 2 == 1) {
                $array[] = $value;
            }
            $encodedValue /= 2;
            $value++;
        }
        return $array;
    }

    /**
     * Encodes multiple integers into a single integer
     *
     * @param array $array
     * @return int
     * @throws \BgaVisibleSystemException
     */
    public static function getValueFromBase16Array(array $array): int
    {
        // Due to the maximum data value of 0x8000000, only 5 elements can be encoded using this function.
        if (count($array) > 5) {
            throw new \BgaVisibleSystemException("setGameStateBase16Array() cannot encode more than 5 integers at once");
        }
        $encodedValue = 0;
        foreach ($array as $value) {
            // This encoding assumes that each integer is in the range [0, 15].
            if ($value < 0 || $value > 15) {
                throw new \BgaVisibleSystemException("setGameStateBase16Array() cannot encode integers smaller than 0 or larger than 15");
            }
            $encodedValue = $encodedValue * 16 + $value;
        }
        return $encodedValue * 6 + count($array);
    }

    /**
     * Decodes an integer representing multiple integers
     *
     * @param int $encodedValue
     * @return array
     */
    public static function getBase16ArrayFromValue(int $encodedValue): array
    {
        $count = $encodedValue % 6;
        $encodedValue /= 6;
        $returnArray = [];
        for ($i = 0; $i < $count; $i++) {
            $returnArray[] = $encodedValue % 16;
            $encodedValue /= 16;
        }
        return $returnArray;
    }

    /**
     * Checks whether the two arrays contain the same elements, regardless of order
     *
     * @param array $a
     * @param array $b
     * @return bool
     */
    public static function isUnorderedEqual(array $a, array $b): bool
    {
        $intersectionCount = count(array_intersect($a, $b));
        return $intersectionCount === count($a) && $intersectionCount === count($b);
    }

    /**
     * Get all repeated values in an array
     *
     * @param array $array The input array
     * @return array The values that appear more than once
     */
    public static function getRepeatedValues(array $array): array
    {
        $counts = array_count_values($array);
        return array_keys(array_filter($counts, function($count) {
            return $count > 1;
        }));
    }

    /**
     * Remove one occurance of the specified element from array
     * 
     * @param array $array The input array
     * @param mixed $element The element to remove  
     * @return array The array with the element removed
     */
    public static function removeElement(array $array, $element): array
    {
        $index = array_search($element, $array);
        if ($index !== false) {
            unset($array[$index]);
        }
        return array_values($array);
    }

}