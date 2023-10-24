<?php

namespace Integration;

use BaseTest;
use BGAWorkbench\Test\TableInstance;
use BGAWorkbench\Test\TableInstanceBuilder;
use BGAWorkbench\Test\TestHelp;
use Doctrine\DBAL\Connection;
use Innovation\Enums\Locations;
use Innovation\Utils\Arrays;

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
    if (strpos($testName, 'artifactsWithRelics')) {
      $artifacts = 3; // enabled with relics
    } else if (strpos($testName, 'artifacts')) {
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
      "game_type"                => 1, // free-for-all (non-2v2)
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
    if (in_array($location, [Locations::DECK, Locations::JUNK, Locations::RELICS, Locations::AVAILABLE_ACHIEVEMENTS])) {
      $playerId = 0;
    } else if ($playerId === null) {
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

  protected function setGlobalVariable(string $name, int $value)
  {
    $this->tableInstance->getTable()->innovationGameState->set($name, $value);
  }

  protected function getGlobalVariable(string $name): int
  {
    return $this->tableInstance->getTable()->innovationGameState->get($name);
  }

  protected function getGlobalVariableAsArray(string $name): array
  {
    return $this->tableInstance->getTable()->innovationGameState->getAsArray($name);
  }

  protected function excecuteInteractions()
  {
    while (self::getCurrentStateName() === 'selectionMove') {
      $choices = [];

      if (count(self::getSelectedCards()) > 0) {
        $choices[] = [$this, 'selectRandomCard'];
      }

      if (!(self::getGlobalVariable('can_pass') == 0 && (self::getGlobalVariable('n_min') > 0 || self::getGlobalVariable('special_type_of_choice') > 0))) {
        $choices[] = [$this, 'pass'];
      }

      if (self::canDoSpecialChoice()) {
        $choices[] = [$this, 'selectSpecialChoice'];
      }

      if (empty($choices)) {
        error_log("ERROR: Player is forced to do something something else, but it's not implemented yet");
      }

      $choice = $choices[array_rand($choices)];
      error_log("  * Chosen interaction: $choice[1]");
      $choice();
    }

    $state = self::getCurrentStateName();
    if (!in_array($state, ['playerTurn', 'gameEnd', 'artifactPlayerTurn', 'promoteCardPlayerTurn', 'dogmaPromotedPlayerTurn'])) {
      error_log("ERROR: Unexpected state after doing interactions: $state");
    }
  }

  protected function canDoSpecialChoice()
  {
    $choiceType = self::getGlobalVariable('special_type_of_choice');

    if ($choiceType <= 0) {
      return false;
    }

    $decodedChoiceType = $this->tableInstance->getTable()->decodeSpecialTypeOfChoice($choiceType);
    switch ($decodedChoiceType) {
      case 'choose_rearrange':
        // I've decided it's not worth to add integration test coverage for the 3rd edition of
        // Publications, which is the only user of 'choose_rearrange'.
        return false;
      case 'choose_yes_or_no':
      case 'choose_non_negative_integer':
        return true;
      case 'choose_from_list':
        return count(self::getGlobalVariableAsArray('choice_array')) > 0;
      case 'choose_value':
        return count(self::getGlobalVariableAsArray('age_array')) > 0;
      case 'choose_color':
      case 'choose_two_colors':
      case 'choose_three_colors':
        return count(self::getGlobalVariableAsArray('color_array')) > 0;
      case 'choose_player':
        return count(self::getGlobalVariableAsArray('player_array')) > 0;
      case 'choose_type':
        return count(self::getGlobalVariableAsArray('type_array')) > 0;
      case 'choose_icon_type':
        return count(self::getGlobalVariableAsArray('icon_array')) > 0;
      case 'choose_special_achievement':
        foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card) {
          if ($card['age'] === null && $card['id'] < 1000) {
            return true;
          }
        }
        foreach (self::getCards(Locations::JUNK) as $card) {
          if ($card['age'] === null && $card['id'] < 1000) {
            return true;
          }
        }
        return false;
      default:
        error_log("WARNING: Unknown special type of choice: $decodedChoiceType");
        return false;
    }
  }

  protected function selectSpecialChoice()
  {
    $choiceType = self::getGlobalVariable('special_type_of_choice');
    $decodedChoiceType = $this->tableInstance->getTable()->decodeSpecialTypeOfChoice($choiceType);

    $choice = null;
    switch ($decodedChoiceType) {
      case 'choose_yes_or_no':
        $choice = rand(0, 1);
        break;
      case 'choose_non_negative_integer':
        $choice = rand(0, 1000);
        break;
      case 'choose_from_list':
        $choice = self::getRandomFromArray(self::getGlobalVariableAsArray('choice_array'));
        break;
      case 'choose_value':
        $choice = self::getRandomFromArray(self::getGlobalVariableAsArray('age_array'));
        break;
      case 'choose_color':
        $choice = self::getRandomFromArray(self::getGlobalVariableAsArray('color_array'));
        break;
      case 'choose_two_colors':
        $chosenColors = self::getRandomElementsFromArray(self::getGlobalVariableAsArray('color_array'), 2);
        $choice = pow(2, $chosenColors[0]) + pow(2, $chosenColors[1]);
        break;
      case 'choose_three_colors':
        $chosenColors = self::getRandomElementsFromArray(self::getGlobalVariableAsArray('color_array'), 3);
        $choice = pow(2, $chosenColors[0]) + pow(2, $chosenColors[1]) + pow(2, $chosenColors[2]);
        break;
      case 'choose_player':
        $playerIndex = self::getRandomFromArray(self::getGlobalVariableAsArray('player_array'));
        $choice = $this->tableInstance->getTable()->playerIndexToPlayerId($playerIndex);
        break;
      case 'choose_type':
        $choice = self::getRandomFromArray(self::getGlobalVariableAsArray('type_array'));
        break;
      case 'choose_icon_type':
        $choice = self::getRandomFromArray(self::getGlobalVariableAsArray('icon_array'));
        break;
      case 'choose_special_achievement':
        $cardIds = [];
        foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card) {
          if ($card['age'] === null && $card['id'] < 1000) {
            $cardIds[] = $card['id'];
          }
        }
        foreach (self::getCards(Locations::JUNK) as $card) {
          if ($card['age'] === null && $card['id'] < 1000) {
            $cardIds[] = $card['id'];
          }
        }
        $choice = self::getRandomFromArray($cardIds);
        break;
      default:
        error_log("WARNING: Unknown special type of choice: $decodedChoiceType");
        break;
    }

    error_log("  * Choice made for $decodedChoiceType: $choice");

    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->stubArg("choice", $choice)
      ->chooseSpecialOption();
    $this->tableInstance->advanceGame();
  }

  protected function getRandomFromArray(array $array): int
  {
    return $array[array_rand($array)];
  }

  protected function getRandomElementsFromArray(array $array, int $numToChoose): array
  {
    $choices = [];
    for ($i = 0; $i < $numToChoose; $i++) {
      $choices[] = self::getRandomFromArray($array);
      $array = Arrays::removeElement($array, $choices[$i]);
    }
    return $choices;
  }

}