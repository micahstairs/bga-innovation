<?php

namespace Integration;

use BaseTest;
use BGAWorkbench\Test\TableInstance;
use Doctrine\DBAL\Connection;

abstract class BaseIntegrationTest extends BaseTest {
  
  protected function getGameOptions(): Array {
    return [
      "game_type" => 1, // non-2v2
      "game_rules" => 1, // 3rd edition
      "artifacts_mode" => 1, // disabled
      "cities_mode" => 1, // disabled
      "echoes_mode" => 1, // disabled
      "unseen_mode" => 1, // disabled
      "extra_achievement_to_win" => 1, // disabled
    ];
  }

  /* Choose a random card from each player's hand and move it to their board */
  protected function chooseRandomCardsForInitialMeld(TableInstance $tableInstance)
  {
    foreach ($this->getPlayerIds($tableInstance) as $playerId) {
      $cards = $tableInstance->getTable()->getCardsInLocation($playerId, "hand");
      $tableInstance
        ->createActionInstanceForCurrentPlayer($playerId)
        ->stubActivePlayerId($playerId)
        ->stubArgs(["card_id" => $cards[0]['id'], "transfer_action" => "meld"])
        ->initialMeld();
    }
    $tableInstance->advanceGame();
  }

  /* Move the card to the player's board and initiate a dogma action */
  protected function meldAndDogma(TableInstance $tableInstance, int $player_id, int $id)
  {
    $tableInstance
      ->createActionInstanceForCurrentPlayer($player_id)
      ->stubActivePlayerId($player_id)
      ->stubArgs(["card_id" => $id, "transfer_action" => "meld"])
      ->debug_transfer();

    $tableInstance
      ->createActionInstanceForCurrentPlayer($player_id)
      ->stubActivePlayerId($player_id)
      ->stubArgs(["card_id" => $id])
      ->dogma();

    $tableInstance->advanceGame();
  }

  protected function getPlayerIds(TableInstance $tableInstance): array
  {
    $players = [];
    $tableInstance->withDbConnection(function (Connection $db) use (&$players) {
      $players = $db->fetchAllAssociative("SELECT player_id FROM player");
    });
    return array_map(function ($player) { return intval($player['player_id']); }, $players);
  }

}
