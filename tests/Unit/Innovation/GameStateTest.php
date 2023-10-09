<?php

namespace Unit\Innovation;

use BaseTest;
use Innovation\Utils\Arrays;
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

    public function testIncrement()
    {
        $this->game->expects($this->once())->method('incGameStateValue')->with('test', 1);
        $this->game->method('getGameStateValue')->with('test')->willReturn(1);

        $this->state->increment('test');
        $this->assertEquals(1, $this->state->get('test'));
    }

    public function testIncrementWithCustomIncrement()
    {
        $this->game->expects($this->once())->method('incGameStateValue')->with('test', 2);
        $this->game->method('getGameStateValue')->with('test')->willReturn(2);

        $this->state->increment('test', 2);
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
        $expectedSetValue = Arrays::encode($input);
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

        $expectedArrayValue = Arrays::decode($input);
        $this->assertEquals($expectedArrayValue, $this->state->getAsArray('test'));
    }

    public function testGetAsArrayFromSetFromArray()
    {
        $input = [1,2,3,4,5];

        $expectedSetValue = Arrays::encode($input);
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

    public function testUsingFirstEditionRules_fourthEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(3);

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

    public function testUsingThirdEditionRules_fourthEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(3);

        $this->assertFalse($this->state->usingThirdEditionRules());
    }

    public function testUsingFourthEditionRules_firstEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(2);

        $this->assertFalse($this->state->usingFourthEditionRules());
    }

    public function testUsingFourthEditionRules_thirdEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(1);

        $this->assertFalse($this->state->usingFourthEditionRules());
    }

    public function testUsingFourthEditionRules_fourthEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(3);

        $this->assertTrue($this->state->usingFourthEditionRules());
    }

    public function testGetEdition_firstEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(2);

        $this->assertEquals(1, $this->state->getEdition());
    }

    public function testGetEdition_thirdEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(1);

        $this->assertEquals(3, $this->state->getEdition());
    }

    public function testGetEdition_fourthEdition()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('game_rules')->willReturn(3);

        $this->assertEquals(4, $this->state->getEdition());
    }

    public function testArtifactsExpansionEnabled_disabled()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('artifacts_mode')->willReturn(1);

        $this->assertFalse($this->state->artifactsExpansionEnabled());
    }

    public function testArtifactsExpansionEnabled_enabledWithoutRelics()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('artifacts_mode')->willReturn(2);

        $this->assertTrue($this->state->artifactsExpansionEnabled());
    }

    public function testArtifactsExpansionEnabled_enabledWithRelics()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('artifacts_mode')->willReturn(3);

        $this->assertTrue($this->state->artifactsExpansionEnabled());
    }

    public function testArtifactsExpansionEnabledWithRelics_disabled()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('artifacts_mode')->willReturn(1);

        $this->assertFalse($this->state->artifactsExpansionEnabledWithRelics());
    }

    public function testArtifactsExpansionEnabledWithRelics_enabledWithoutRelics()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('artifacts_mode')->willReturn(2);

        $this->assertFalse($this->state->artifactsExpansionEnabledWithRelics());
    }

    public function testArtifactsExpansionEnabledWithRelics_enabledWithRelics()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('artifacts_mode')->willReturn(3);

        $this->assertTrue($this->state->artifactsExpansionEnabledWithRelics());
    }

    public function testCitiesExpansionEnabled_disabled()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('cities_mode')->willReturn(1);

        $this->assertFalse($this->state->citiesExpansionEnabled());
    }

    public function testCitiesExpansionEnabled_enabled()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('cities_mode')->willReturn(2);

        $this->assertTrue($this->state->citiesExpansionEnabled());
    }

    public function testEchoesExpansionEnabled_disabled()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('echoes_mode')->willReturn(1);

        $this->assertFalse($this->state->echoesExpansionEnabled());
    }

    public function testEchoesExpansionEnabled_enabled()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('echoes_mode')->willReturn(2);

        $this->assertTrue($this->state->echoesExpansionEnabled());
    }

    public function testUnseenExpansionEnabled_disabled()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('unseen_mode')->willReturn(1);

        $this->assertFalse($this->state->unseenExpansionEnabled());
    }

    public function testUnseenExpansionEnabled_enabled()
    {
        $this->game->expects($this->once())->method('getGameStateValue')->with('unseen_mode')->willReturn(2);

        $this->assertTrue($this->state->unseenExpansionEnabled());
    }
}
