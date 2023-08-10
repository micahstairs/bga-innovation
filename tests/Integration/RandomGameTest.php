<?php

namespace Integration\Cards\Base;

use Innovation\Utils\Arrays;
use Integration\BaseIntegrationTest;

class RandomGameTest extends BaseIntegrationTest
{
  public function test_randomGame_thirdEdition_cities_echoes()
  {
    self::executeGame();
  }

  public function test_randomGame_fourthEdition_cities_echoes_unseen()
  {
    self::executeGame();
  }

  private function executeGame() {
    while (self::getCurrentStateName() !== 'gameEnd') {
      $actions = [
        [$this, 'draw'],
      ];
      if (self::countCards('hand') > 0) {
        $actions[] = [$this, 'meld'];
      }
      if (count(self::getCardsToDogma()) > 0) {
        $actions[] = [$this, 'dogma'];
      }
      // TODO: Add other actions here (e.g. achieve, endorse, etc.)
      $actions[array_rand($actions)]();
    }

    // TODO: Add better game end info (and stop hard-coding the player numbers)
    error_log("*** GAME OVER ***");
    error_log("Player #1 score: " . $this->tableInstance->getTable()->getPlayerScore(12345));
    error_log("Player #1 achievements: " . $this->tableInstance->getTable()->countCardsInLocation(12345, 'achievements'));
    error_log("Player #2 score: " . $this->tableInstance->getTable()->getPlayerScore(67890));
    error_log("Player #2 achievements: " . $this->tableInstance->getTable()->countCardsInLocation(67890, 'achievements'));
  }

  private function draw()
  {
    error_log("* DRAW");
    $this->tableInstance
        ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
        ->draw();
    $this->tableInstance->advanceGame();
  }

  private function meld()
  {
    $cardId = self::getRandomCardId(self::getCards('hand'));
    $cardName = $this->tableInstance->getTable()->getCardName($cardId);
    error_log("* MELD $cardName");
    $this->tableInstance
        ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
        ->stubArg('card_id', $cardId)
        ->meld();
    $this->tableInstance->advanceGame();

    if (self::getCurrentStateName() === 'promoteCardPlayerTurn') {
      $promotedCardId = self::getRandomCardId(self::getCards('forecast'));
      $promotedCardName = $this->tableInstance->getTable()->getCardName($cardId);
      error_log("* PROMOTE $promotedCardName");
      $this->tableInstance
        ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
        ->stubArg('card_id', $promotedCardId)
        ->promoteCard();
      $this->tableInstance->advanceGame();
    }

    if (self::getCurrentStateName() === 'promoteDogmaPlayerTurn') {
      $this->tableInstance
        ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
        ->dogmaPromotedCard();
      $this->tableInstance->advanceGame();
    }

    self::excecuteInteractions();
  }

  private function dogma()
  {
    $cardId = self::getRandomCardId(self::getCardsToDogma());
    $cardName = $this->tableInstance->getTable()->getCardName($cardId);
    error_log("* DOGMA $cardName");
    $this->tableInstance
        ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
        ->stubArg('card_id', $cardId)
        ->dogma();
    $this->tableInstance->advanceGame();

    self::excecuteInteractions();
  }

  private function excecuteInteractions()
  {
    do {
      while (self::getCurrentStateName() === 'selectionMove') {
        $choices = [];

        if (count(self::getSelectedCards()) > 0) {
          $choices[] = [$this, 'selectRandomCard'];
        }

        if (!(self::getGlobalVariable('can_pass') == 0 && self::getGlobalVariable('n_min') > 0)) {
          $choices[] = [$this, 'pass'];
        }

        if (self::getGlobalVariable('special_type_of_choice') > 0) {
          $choices[] = [$this, 'selectSpecialChoice'];
        }

        if (empty($choices)) {
          error_log("ERROR: Player is forced to do something something else, but it's not implemented yet");
        }
        $choices[array_rand($choices)]();
      }
    } while (self::getCurrentStateName() != 'playerTurn' && self::getCurrentStateName() != 'gameEnd');
  }

  private function selectSpecialChoice()
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
      default:
        error_log("WARNING: Unknown special type of choice: $decodedChoiceType");
        break;
    }

    $this->tableInstance
      ->createActionInstanceForCurrentPlayer(self::getActivePlayerId())
      ->stubArg("choice", $choice)
      ->chooseSpecialOption();
    $this->tableInstance->advanceGame();
  }
  
  private function getRandomFromArray(array $array): int {
    return $array[array_rand($array)];
  }

  private function getRandomElementsFromArray(array $array, int $numToChoose): array {
    $choices = [];
    for ($i = 0; $i < $numToChoose; $i++) {
      $choices[] = self::getRandomFromArray($array);
      $array = Arrays::removeElement($array, $choices[$i]);
    }
    return $choices;
  }
}
