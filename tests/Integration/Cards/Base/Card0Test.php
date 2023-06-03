<?php

namespace Integration\Cards\Base;

use Doctrine\DBAL\Connection;
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

    $game = $this->table->setupNewGame();

    // return card if it's not in player's hand

    $action = $game
      ->createActionInstanceForCurrentPlayer(self::getPlayer1())
      ->stubActivePlayerId(self::getPlayer1())
      ->stubArgs(["card_id" => 0]);
    
    // $action->meld();
    // $action->dogma();

  //   $game->withDbConnection(function (Connection $db) {
  //     $id = 0;
  //     $result = $db->executeQuery("SELECT * FROM card WHERE id = {$id}");
  //     if ($result->rowCount() === 0) {
  //       throw new \RuntimeException("No card found with id {$id}");
  //     }
  //     $res = $result->fetch();
  //     throw new \RuntimeException(gettype($res) . " " . implode(", ", $res));
  // });

  }
}
