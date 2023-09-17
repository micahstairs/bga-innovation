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
    error_log("*** SETTING UP NEW GAME ***");
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
    $artifacts = 1; // disabled
    if (strpos($testName, 'artifacts')) {
      $artifacts = 2; // enabled
    }
    $cities = 1; // disabled
    if (strpos($testName, 'cities')) {
      $cities = 2; // enabled
    }
    $echoes = 1; // disabled
    if (strpos($testName, 'echoes')) {
      $echoes = 2; // enabled
    }
    $unseen = 1; // disabled
    if (strpos($testName, 'unseen')) {
      $unseen = 2; // enabled
    }

    return [
      "game_type"                => 1, // non-2v2
      "game_rules"               => $game_rules,
      "artifacts_mode"           => $artifacts,
      "cities_mode"              => $cities,
      "echoes_mode"              => $echoes,
      "unseen_mode"              => $unseen,
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

  /* Choose a random card from the currently selected cards */
  protected function selectRandomCard()
  {
    $playerId = self::getActivePlayerId();
    $cards = self::getSelectedCards();
    $this->tableInstance
      ->createActionInstanceForCurrentPlayer($playerId)
      ->stubActivePlayerId($playerId)
      ->stubArgs(["card_id" => self::getRandomCardId($cards)])
      ->choose();
    $this->tableInstance->advanceGame();
  }

  protected function getSelectedCards(): array
  {
    return $this->tableInstance->getTable()->getSelectedCards();
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

  protected function countCards(string $location, int $playerId = null): int
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    return $this->tableInstance->getTable()->countCardsInLocation($playerId, $location);
  }

  protected function getCards(string $location, int $playerId = null): array
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    return $this->tableInstance->getTable()->getCardsInLocation($playerId, $location);
  }

  protected function getCardsToDogma(int $playerId = null): array
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }

    $cards = $this->tableInstance->getTable()->getTopCardsOnBoard($playerId);

    $cardsToDogma = [];
    foreach ($cards as $card) {
      if ($card['dogma_icon']) {
        $cardsToDogma[] = $card;
      }
    }

    return $cardsToDogma;
  }

  protected function getCardsToPromote(int $playerId = null): array
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }

    $meldedCard = $this->tableInstance->getTable()->getCardInfo(self::getGlobalVariable('melded_card_id'));

    $cardsToPromote = [];
    foreach (self::getCards('forecast', $playerId) as $card) {
      if ($card['age'] <= $meldedCard['age']) {
        $cardsToPromote[] = $card;
      }
    }

    return $cardsToPromote;
  }

  protected function getClaimableStandardAchievementValues(int $playerId = null): array
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    return $this->tableInstance->getTable()->getClaimableStandardAchievementValues($playerId);
  }

  protected function getClaimableSecretValues(int $playerId = null): array
  {
    if ($playerId === null) {
      $playerId = self::getActivePlayerId();
    }
    return $this->tableInstance->getTable()->getClaimableSecretValues($playerId);
  }

  protected function getActivePlayerId(): int
  {
    return $this->tableInstance->getTable()->getActivePlayerId();
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

  protected function getGlobalVariable(string $name): int
  {
    return $this->tableInstance->getTable()->innovationGameState->get($name);
  }

  protected function getGlobalVariableAsArray(string $name): array
  {
    return $this->tableInstance->getTable()->innovationGameState->getAsArray($name);
  }

}