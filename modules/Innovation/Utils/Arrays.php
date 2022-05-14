<?php

namespace Innovation\Utils;

class Arrays
{
    /**
     * @param array $array
     * @return int
     */
    public static function getArrayAsValue(array $array): int
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
    public static function getValueAsArray(int $encodedValue): array
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

}
