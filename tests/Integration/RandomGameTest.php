<?php

namespace Integration\Cards\Base;

use Integration\BaseIntegrationTest;

class RandomGameTest extends BaseIntegrationTest
{
  public function test_randomGame()
  {
    while (self::getCurrentStateName() !== 'gameEnd') {
      self::draw();
    }
  }

  private function draw()
  {
    $playerId = self::getActivePlayerId();
    $this->tableInstance
        ->createActionInstanceForCurrentPlayer($playerId)
        ->stubActivePlayerId($playerId)
        ->draw();
    $this->tableInstance->advanceGame();
  }

}
