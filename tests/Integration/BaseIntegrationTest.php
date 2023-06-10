<?php

namespace Integration;

use BaseTest;
use BGAWorkbench\Test\TableInstance;
use BGAWorkbench\Test\TableInstanceBuilder;
use BGAWorkbench\Test\TestHelp;
use Doctrine\DBAL\Connection;

abstract class BaseIntegrationTest extends BaseTest
{
  use TestHelp;

  /**
   * @var TableInstance
   */
  protected $tableInstance;

  protected function setUp(): void
  {
    $this->tableInstance = $this->createGameTableInstanceBuilder()
      ->build()
      ->createDatabase()
      ->setupNewGame();
    $this->chooseRandomCardsForInitialMeld();
  }

  protected function tearDown(): void
  {
    if ($this->tableInstance !== null) {
      $this->tableInstance->dropDatabaseAndDisconnect();
    }
  }

  protected function createGameTableInstanceBuilder(): TableInstanceBuilder
  {
    return $this->gameTableInstanceBuilder()
      ->setPlayersWithIds([self::getPlayer1(), self::getPlayer2()])
      ->overrideGlobalsPreSetup(self::getGameOptions());
  }

  protected function getPlayer1(): int
  {
    return 12345;
  }

  protected function getPlayer2(): int
  {
    return 67890;
  }

  protected function getGameOptions(): array
  {
    return [
      "game_type"                => 1, // non-2v2
      "game_rules"               => 1, // 3rd edition
      "artifacts_mode"           => 1, // disabled
      "cities_mode"              => 1, // disabled
      "echoes_mode"              => 1, // disabled
      "unseen_mode"              => 1, // disabled
      "extra_achievement_to_win" => 1, // disabled
    ];
  }

  /* Choose a random card from each player's hand and move it to their board */
  protected function chooseRandomCardsForInitialMeld()
  {
    foreach ($this->getPlayerIds() as $playerId) {
      $cards = $this->tableInstance->getTable()->getCardsInLocation($playerId, "hand");
      $this->tableInstance
        ->createActionInstanceForCurrentPlayer($playerId)
        ->stubActivePlayerId($playerId)
        ->stubArgs(["card_id" => $cards[0]['id'], "transfer_action" => "meld"])
        ->initialMeld();
    }
    $this->tableInstance->advanceGame();
  }

  /* Move the card to the player's board and initiate a dogma action */
  protected function meldAndDogma(int $playerId, int $id)
  {
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => $id, "transfer_action" => "meld"])
      ->debug_transfer();

    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => $id])
      ->dogma();

    $this->tableInstance->getTable()->stubCurrentPlayerId($playerId);
    $this->tableInstance->advanceGame();
  }

  /* Select a random card in the specified location */
  protected function selectRandomCard(int $playerId, string $location)
  {
    $cards = $this->tableInstance->getTable()->getCardsInLocation($playerId, $location);
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => $cards[0]['id']])
      ->choose();
    $this->tableInstance->advanceGame();
  }

  /* Choose to pass */
  protected function pass(int $playerId)
  {
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => -1])
      ->choose();
    $this->tableInstance->advanceGame();
  }

  protected function assertDogmaComplete(): void
  {
    self::assertEquals("playerTurn", $this->tableInstance->getTable()->getCurrentState()['name']);
  }

  protected function getScore(int $playerId): int
  {
    return $this->tableInstance->getTable()->getPlayerScore($playerId);
  }

  protected function getPlayerIds(): array
  {
    $players = [];
    $this->tableInstance->withDbConnection(function (Connection $db) use (&$players) {
      $players = $db->fetchAllAssociative("SELECT player_id FROM player");
    });
    return array_map(function ($player) {
      return intval($player['player_id']); }, $players);
  }

}