<?php

namespace Unit;

class GameTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testGetArrayAsValue()
    {
        $array = [2,3,4];
        $game = $this->getInnovationInstance();
        $this->assertEquals(28, $game->getArrayAsValue($array));
    }
}
