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

  // EXAMPLE: This is how you can get data from the database
  protected function getCard(TableInstance $tableInstance, int $id): array
  {
    $card = null;
    $tableInstance->withDbConnection(function (Connection $db) use (&$card, $id) {
      $card = $db->fetchAssociative("SELECT * FROM card WHERE id = ?", [$id]);
    });
    return $card;
  }

  /* Move the card to the player's board in preparation for a dogma action */
  protected function prepareCardForDogma(TableInstance $tableInstance, int $player_id, int $id)
  {
    $card = $tableInstance->getTable()->getCardInfo($id);
    $card['using_debug_buttons'] = true;
    if ($card['owner'] !== $player_id && $card['location'] !== 'deck') {
      $card = $tableInstance->getTable()->returnCard($card);
      $card['using_debug_buttons'] = true;
    }
    if ($card['location'] === 'deck') {
      $card = $tableInstance->getTable()->transferCardFromTo($card, $player_id, 'hand');
      $card['using_debug_buttons'] = true;
    }
    if ($card['location'] !== 'board') {
      $card = $tableInstance->getTable()->meldCard($card, $player_id);
    }


    // $action = $tableInstance
    // ->createActionInstanceForCurrentPlayer($player_id)
    // ->stubActivePlayerId($player_id)
    // ->stubArgs(["card_id" => $id, "transfer_action" => "meld"])
    // ->debug_transfer();

    // $card = $tableInstance->getTable()->getCardInfo($id);
    $real_card = self::getCard($tableInstance, $id);
    if ($card['location'] !== 'board' || $real_card['location'] !== 'board') {
      throw new \Exception("not melded! " . $card['location'] . " " . $real_card['location']);
    }
  }

}
