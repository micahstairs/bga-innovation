<?php

namespace Integration\Cards\Base;

use Integration\BaseIntegrationTest;

class RandomGameTest extends BaseIntegrationTest
{
  public function test_randomGame()
  {
    while (self::getCurrentStateName() !== 'gameEnd') {
      $actions = [
        [$this, 'draw'],
      ];
      if (self::countCards('hand') > 0) {
        $actions[] = [$this, 'meld'];
      }

      // Perform random action
      $actions[array_rand($actions)]();
    }
  }

  private function draw()
  {
    $this->tableInstance
        ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
        ->draw();
    $this->tableInstance->advanceGame();
  }

  private function meld()
  {
    $this->tableInstance
        ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
        ->stubArg('card_id', self::getRandomCardId(self::getCards('hand')))
        ->meld();
    $this->tableInstance->advanceGame();
  }

}
