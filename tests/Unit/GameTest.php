<?php

namespace Innovation\Tests;

class GameTest extends BaseTest
{
    public function setUp()
    {
        parent::setUp();
        require 'innovation.game.php';
    }

    public function testGetArrayAsValue()
    {
        $array = [2,3,4];
        $game = new \Innovation();
        $this->assertEquals(28, $game->getArrayAsValue($array));
    }
}
