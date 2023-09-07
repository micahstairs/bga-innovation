<?php

namespace Integration\Cards;

use Integration\BaseIntegrationTest;
use BGAWorkbench\Test\TableInstance;
use BGAWorkbench\Test\TableInstanceBuilder;
use Doctrine\DBAL\Connection;

abstract class BaseCardIntegrationTest extends BaseIntegrationTest
{

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

    $this->chooseRandomCardsForInitialMeld();
    $this->returnAllCardsOnBoardsToHand();

    $this->meld();
    self::setHandSize(2);
  }

  protected function getInitialHandSize(): int
  {
    return 2;
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

  /* Try to pass (but skip if no interaction is required) */
  protected function passIfNeeded()
  {
    if (self::getCurrentStateName() === "selectionMove") {
      $this->pass();
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

  /* Draw a base card of the specified value */
  protected function drawBaseCard(int $age, int $playerId = null): array
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    $card = $this->tableInstance->getTable()->getDeckTopCard($age, \Innovation\Enums\CardTypes::BASE);
    $this->tableInstance
        ->createActionInstanceForCurrentPlayer($playerId)
        ->stubArgs(["card_id" => $card["id"], "transfer_action" => "draw"])
        ->debug_transfer();
    return $this->tableInstance->getTable()->getCardInfo($card['id']);
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

  protected function assertCardInLocation(int $cardId, string $location, int $playerId = null): void
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    $card = $this->tableInstance->getTable()->getCardInfo($cardId);
    self::assertEquals($location, $card['location']);
    self::assertEquals($playerId, $card['owner']);
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

  protected function assertDogmaComplete(): void
  {
    self::assertEquals("playerTurn", self::getCurrentStateName());
  }

  /* Returns the ID of the card associated with the test (assumes CardXTest naming, where X is the card ID) */
  protected function getCardIdFromTestClassName(): int
  {
    $className = get_class($this);
    return intval(substr($className, strrpos($className, "\\") + 5, -4));
  }

}