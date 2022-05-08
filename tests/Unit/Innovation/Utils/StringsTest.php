<?php

namespace Unit\Innovation\Utils;

use Unit\BaseTest;
use Innovation\Utils\Strings;

class StringsTest extends BaseTest
{
    /**
     * @dataProvider doesStringComeBeforeProvider
     */
    public function testDoesStringComeBefore($str1, $str2, $comesBefore)
    {
        $this->assertEquals($comesBefore, Strings::doesStringComeBefore($str1, $str2));
    }

    public function doesStringComeBeforeProvider(): array
    {
        return [
            ['abacus', 'zebra', true], # alpha precedence
            ['zebra', 'abacus', false], # alpha precedence, reverse case
            ['1vision', 'abacus', true], # numbers prior to letters
            ['aaaa', '1abc', false], # numbers prior to letters, reverse case
            ['aaa', 'abbb', true], # alphabet precedence for second letter
            ['aaa', 'a11', false], # second numbers over letters
            ['A Big Card', 'ABC Card', false], # spaces _after_ letters
            ['Code of Laws', 'Pottery', true], # Real example
            ['Sailing', 'São Paulo', true], # accents ignored
            ['São Paulo', 'Satellites', true], # accents ignored
        ];
    }
}
