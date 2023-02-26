<?php

namespace Unit\Innovation\Utils;

use Unit\BaseTest;
use Innovation\Utils\Arrays;

class ArraysTest extends BaseTest
{

    public function testFlatten()
    {
        $unflattened = [[10, 11], [20, 21, 22], []];
        $flattened = Arrays::flatten($unflattened);
        $this->assertEquals([10, 11, 20, 21, 22], $flattened);
    }

    public function testGetArrayAsValue()
    {
        $a = [10, 20, 30];
        $v = Arrays::getArrayAsValue($a);
        $this->assertEquals(1074791424, $v);
    }

    public function testGetValueAsArray()
    {
        $v = Arrays::getValueAsArray(1074791424);
        $this->assertEquals([10, 20, 30], $v);
    }

    public function testSetAsArrayAndGetAsArray()
    {
        $original = [1,2,3,4,5];
        $output = Arrays::getValueAsArray(Arrays::getArrayAsValue($original));
        $this->assertEmpty(array_diff($original, $output));
    }

    /**
     * @dataProvider providerTestGetValueFromBase16Array
     */
    public function testGetValueFromBase16Array($expected, $inputArray)
    {
        $this->assertEquals($expected, Arrays::getValueFromBase16Array($inputArray));
    }
    public function providerTestGetValueFromBase16Array(): array
    {
        return [
            [447395, [1,2,3,4,5]],
        ];
    }

    public function testGetValueFromBase16ArrayWithTooManyItems()
    {
        $this->expectException(\BgaVisibleSystemException::class);
        $this->expectExceptionMessage('setGameStateBase16Array() cannot encode more than 5 integers at once');
        Arrays::getValueFromBase16Array([1,2,3,4,5,6]);
    }

    public function testGetValueFromBase16ArrayWithInvalidInputRange()
    {
        $this->expectException(\BgaVisibleSystemException::class);
        $this->expectExceptionMessage('setGameStateBase16Array() cannot encode integers smaller than 0 or larger than 15');
        Arrays::getValueFromBase16Array([1,16,2]);
    }

    /**
     * @dataProvider providerTestGetBase16ArrayFromValue
     */
    public function testGetBase16ArrayFromValue($expected, $inputValue)
    {
        $this->assertEquals($expected, Arrays::getBase16ArrayFromValue($inputValue));
    }
    public function providerTestGetBase16ArrayFromValue(): array
    {
        return [
            [[5,4,3,2,1], 447395],
        ];
    }

    public function testSetBase16ArrayAndGetBase16Array()
    {
        $original = [1,2,3,4,5];
        $output = Arrays::getBase16ArrayFromValue(Arrays::getValueFromBase16Array($original));
        $this->assertEmpty(array_diff($original, $output));
    }
}
