<?php

namespace Unit\Innovation;

use Innovation\Utils\Arrays;
use Unit\BaseTest;
use Innovation\GameState;

class GameStateTest extends BaseTest
{
    protected function setUp(): void
    {
        $this->game = $this->getMockBuilder(\stdClass::class)
            ->addMethods([
                'setGameStateValue',
                'getGameStateValue',
                'incGameStateValue',
                'setGameStateInitialValue',
            ])
            ->getMock();
        $this->state = new GameState($this->game);
    }

    public function testGet()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('foo')->willReturn('bar');

        $this->assertEquals('bar', $this->state->get('foo'));
    }

    public function testSet()
    {
        $this->game->expects($this->once())->method('setGameStateValue')->with('test', 1);
        $this->game->method('getGameStateValue')->with('test')->willReturn(1);

        $this->state->set('test', 1);
        $this->assertEquals(1, $this->state->get('test'));
    }

    public function testInc()
    {
        $this->game->expects($this->once())->method('incGameStateValue')->with('test', 1);
        $this->game->method('getGameStateValue')->with('test')->willReturn(1);

        $this->state->inc('test');
        $this->assertEquals(1, $this->state->get('test'));
    }

    public function testIncWithCustomIncrement()
    {
        $this->game->expects($this->once())->method('incGameStateValue')->with('test', 2);
        $this->game->method('getGameStateValue')->with('test')->willReturn(2);

        $this->state->inc('test', 2);
        $this->assertEquals(2, $this->state->get('test'));
    }

    public function testSetInitial()
    {
        $this->game->expects($this->once())->method('setGameStateInitialValue')->with('test', 1);
        $this->game->method('getGameStateValue')->with('test')->willReturn(1);

        $this->state->setInitial('test', 1);
        $this->assertEquals(1, $this->state->get('test'));
    }

    public function testSetFromArray()
    {
        $input = [1,2,3,4,5];
        $expectedSetValue = Arrays::getArrayAsValue($input);
        $this->game->expects($this->once())->method('setGameStateValue')->with('test', $expectedSetValue);
        $this->game->method('getGameStateValue')->with('test')->willReturn($expectedSetValue);

        $this->state->setFromArray('test', $input);
        $this->assertEquals($expectedSetValue, $this->state->get('test'));
    }

    public function testGetAsArray()
    {
        $input = 447395;
        $this->game->method('getGameStateValue')->with('test')->willReturn($input);

        $this->state->set('test', $input);

        $expectedArrayValue = Arrays::getValueAsArray($input);
        $this->assertEquals($expectedArrayValue, $this->state->getAsArray('test'));
    }

    public function testGetAsArrayFromSetFromArray()
    {
        $input = [1,2,3,4,5];

        $expectedSetValue = Arrays::getArrayAsValue($input);
        $this->game->expects($this->once())->method('setGameStateValue')->with('test', $expectedSetValue);
        $this->game->method('getGameStateValue')->with('test')->willReturn($expectedSetValue);

        $this->state->setFromArray('test', $input);
        $this->assertEquals($input, $this->state->getAsArray('test'));
    }

    public function testUsingFirstEditionRules_firstEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(2);

        $this->assertTrue($this->state->usingFirstEditionRules());
    }

    public function testUsingFirstEditionRules_thirdEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(1);

        $this->assertFalse($this->state->usingFirstEditionRules());
    }

    public function testUsingThirdEditionRules_firstEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(2);

        $this->assertFalse($this->state->usingThirdEditionRules());
    }

    public function testUsingThirdEditionRules_thirdEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(1);

        $this->assertTrue($this->state->usingThirdEditionRules());
    }
}
