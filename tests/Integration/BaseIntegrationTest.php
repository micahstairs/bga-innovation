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

  /**
   * Set up a new game of Innovation.
   * 
   * Assumptions that tests can make about the setup:
   *  - The first player will have exactly one card on their board (the card under test).
   *  - The other player will have no cards on their board.
   *  - Both players will have two 1's in hand.
   */
  protected function setUp(): void
  {
    $this->tableInstance = $this->createGameTableInstanceBuilder()
      ->build()
      ->createDatabase()
      ->setupNewGame();

    // Meld initial cards then pull them back into hand
    $this->chooseRandomCardsForInitialMeld();
    $this->returnAllCardsOnBoardsToHand();

    // Place the card under test on the first player's board and replenish their hand
    $this->meld();
    self::setHandSize(2);
  }

  protected function getInitialHandSize(): int
  {
    return 2;
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
      ->setPlayersWithIds([12345, 67890])
      ->overrideGlobalsPreSetup(self::getGameOptions());
  }

  protected function getGameOptions(): array
  {
    $testName = $this->getName();

    // Randomly pick 3rd or 4th edition (unless the test name specifies a specific edition)
    $game_rules = array_rand([1,3]);
    if (strpos($testName, 'thirdEdition')) {
      $game_rules = 1;
    }
    if (strpos($testName, 'fourthEdition')) {
      $game_rules = 3;
    }

    return [
      "game_type"                => 1, // non-2v2
      "game_rules"               => $game_rules,
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
        ->stubArgs(["card_id" => self::getRandomCardId($cards), "transfer_action" => "meld"])
        ->initialMeld();
    }
    $this->tableInstance->advanceGame();
  }

  /* Return all cards on the player's board to their hand */
  protected function returnAllCardsOnBoardsToHand()
  {
    foreach ($this->getPlayerIds() as $playerId) {
      $cards = $this->tableInstance->getTable()->getCardsInLocation($playerId, "board");
      foreach ($cards as $card) {
        $this->tableInstance
          ->createActionInstanceForCurrentPlayer($playerId)
          ->stubArgs(["card_id" => $card["id"], "transfer_action" => "draw"])
          ->debug_transfer();
      }
    }
  }

  /* Move the card to the player's board. If the ID is null, it's assumed the card under test is the one that should be melded. */
  protected function meld(int $cardId = null, int $playerId = null)
  {
    if ($cardId === null) {
      $cardId = self::getCardIdFromTestClassName();
    }
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => $cardId, "transfer_action" => "meld"])
      ->debug_transfer();
  }

  /* Initiate a dogma action (assumes the card is on the player's board). If the ID is null, it's assumed the card under test is the one that should be dogma'd. */
  protected function dogma(int $id = null)
  {
    if ($id === null) {
      $id = self::getCardIdFromTestClassName();
    }
    $playerId = self::getActivePlayerId();
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => $id])
      ->dogma();
    $this->tableInstance->getTable()->stubCurrentPlayerId($playerId);
    $this->tableInstance->advanceGame();
  }

  /* Choose a random card from the currently selected cards */
  protected function selectRandomCard()
  {
    $playerId = self::getActivePlayerId();
    $cards = $this->tableInstance->getTable()->getSelectedCards();
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => self::getRandomCardId($cards)])
      ->choose();
    $this->tableInstance->advanceGame();
  }

  /* Select the specified card */
  protected function selectCard(int $cardId)
  {
    $playerId = self::getActivePlayerId();
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => $cardId])
      ->choose();
    $this->tableInstance->advanceGame();
  }

  /* Choose to pass */
  protected function pass()
  {
    $playerId = self::getActivePlayerId();
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => -1])
      ->choose();
    $this->tableInstance->advanceGame();
  }

  /* Try to pass (but skip if no interaction is required) */
  protected function passIfNeeded()
  {
    if (self::getCurrentStateName() === "selectionMove") {
      $this->pass();
    }
  }

  /* Return cards or draw 1s until the hand is of the specified size */
  protected function setHandSize(int $targetSize)
  {
    $playerId = self::getActivePlayerId();
    $currentSize = $this->tableInstance->getTable()->countCardsInLocation($playerId, "hand");
    while ($currentSize > $targetSize) {
      $cards = $this->tableInstance->getTable()->getCardsInLocation($playerId, "hand");
      $this->tableInstance
        ->createActionInstanceForCurrentPlayer($playerId)
        ->stubActivePlayerId($playerId)
        ->stubArgs(["card_id" => self::getRandomCardId($cards), "transfer_action" => "return"])
        ->debug_transfer();
      $currentSize--;
    }
    while ($currentSize < $targetSize) {
      $this->tableInstance->getTable()->executeDraw($playerId, 1);
      $currentSize++;
    }
  }

  /* Draw a base card of the specified value */
  protected function drawBaseCard(int $age, int $playerId = null): array
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    $card = $this->tableInstance->getTable()->getDeckTopCard($age, \Innovation::BASE);
    $this->tableInstance
        ->createActionInstanceForCurrentPlayer($playerId)
        ->stubArgs(["card_id" => $card["id"], "transfer_action" => "draw"])
        ->debug_transfer();
    return $this->tableInstance->getTable()->getCardInfo($card['id']);
  }

  protected function assertDogmaComplete(): void
  {
    self::assertEquals("playerTurn", self::getCurrentStateName());
  }

  protected function getMaxAgeOnBoard(int $playerId = null): int
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    return $this->tableInstance->getTable()->getMaxAgeOnBoardTopCards($playerId);
  }

  protected function getMaxAge(string $location, int $playerId = null): int
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    $cards = $this->tableInstance->getTable()->getCardsInLocation($playerId, $location);
    $maxAge = 0;
    foreach ($cards as $card) {
      $maxAge = max($maxAge, $card['age']);
    }
    return $maxAge;
  }

  protected function getScore(int $playerId = null): int
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    return $this->tableInstance->getTable()->getPlayerScore($playerId);
  }

  protected function countCards(string $location, int $playerId = null): int
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    return $this->tableInstance->getTable()->countCardsInLocation($playerId, $location);
  }

  protected function assertCardInLocation(int $cardId, string $location, int $playerId = null): void
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    $card = $this->tableInstance->getTable()->getCardInfo($cardId);
    self::assertEquals($location, $card['location']);
    self::assertEquals($playerId, $card['owner']);
  }

  protected function getActivePlayerId(): int
  {
    return $this->tableInstance->getTable()->getActivePlayerId();
  }

  protected function getNonActivePlayerId(): int
  {
    $activePlayerId = self::getActivePlayerId();
    foreach (self::getPlayerIds() as $playerId) {
      if ($playerId !== $activePlayerId) {
        return $playerId;
      }
    }
    throw new \Exception("No non-active player found");
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

  protected function getCurrentStateName(): string
  {
    return $this->tableInstance->getTable()->getCurrentState()['name'];
  }

  protected function getRandomCardId(array $cards): int
  {
    return $cards[array_rand($cards)]['id'];
  }

  /* Returns the ID of the card associated with the test (assumes CardXTest naming, where X is the card ID) */
  protected function getCardIdFromTestClassName(): int
  {
    $className = get_class($this);
    return intval(substr($className, strrpos($className, "\\") + 5, -4));
  }

}