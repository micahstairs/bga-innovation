<?php

namespace Integration\Cards\Base;

use Helpers\FakeGame;
use Integration\BaseIntegrationTest;
use Integration\GameSetup;
use Innovation\Cards\Base\Card0;

class Card0Test extends BaseIntegrationTest
{
  use GameSetup;

  // NOTE: This overrides the default game options
  protected function getGameOptions(): Array
  {
    return [
      "game_type" => 1, // non-2v2
      "game_rules" => 2, // 4th edition
      "artifacts_mode" => 1, // disabled
      "cities_mode" => 1, // disabled
      "echoes_mode" => 1, // disabled
      "unseen_mode" => 1, // disabled
      "extra_achievement_to_win" => 1, // disabled
    ];
  }

  public function testDogma()
  {
    $tableInstance = $this->table->setupNewGame();
    self::chooseRandomCardsForInitialMeld($tableInstance);
    self::meldAndDogma($tableInstance, self::getPlayer1(), 0);
    // TODO: Assert that the dogma worked.
  }
}
