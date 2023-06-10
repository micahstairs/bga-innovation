<?php

namespace Unit;

use BaseTest;

class GameTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testIntDivision()
    {
        $game = $this->getInnovationInstance();
        $this->assertEquals(2, $game->intDivision(7, 3));
        $this->assertEquals(3, $game->intDivision(6, 2));
        $this->assertEquals(4, $game->intDivision(4, 1));
    }
}
