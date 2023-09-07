<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card547 extends Card
{

  // Camouflage:
  //   - Choose to either junk exactly two top cards of different color and equal value on your
  //     board, then safeguard them, or score exactly two of your secrets of equal value.
  //   - Draw a [7] for each special achievement you have.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(3);
    } else {
      foreach (self::getCards('achievements') as $card) {
        if (self::isSpecialAchievement($card)) {
          self::draw(7);
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return ['choices' => [0, 1]];
    } else if (self::isSecondInteraction()) {
      if (self::getAuxiliaryValue() === 1) {
        return [
          'location_from' => 'board',
          'location_to'   => 'junk,safe',
          'color'         => $this->game->getColorsOfRepeatedValueOfTopCardsOnBoard(self::getPlayerId()),
        ];
      } else {
        $this->game->setAuxiliaryArray(self::getCardIdsWithDuplicateValuesInLocation('safe'));
        return [
          'location_from'                   => 'safe',
          'score_keyword'                   => true,
          'card_ids_are_in_auxiliary_array' => true,
        ];
      }
    } else {
      if (self::getAuxiliaryValue() === 1) {
        return [
          'location_from' => 'board',
          'location_to'   => 'junk,safe',
          'age'           => self::getLastSelectedAge(),
          'color'         => Colors::getAllColorsOtherThan(self::getLastSelectedColor()),
        ];
      } else {
        return [
          'location_from' => 'safe',
          'score_keyword' => true,
          'age'           => self::getLastSelectedAge(),
        ];
      }
    }
  }

  protected function getPromptForListChoice(): array
  {
    return self::buildPromptFromList([
      0 => clienttranslate('Score two of your secrets'),
      1 => clienttranslate('Junk and safeguard two of your top cards'),
    ]);
  }

  public function handleSpecialChoice(int $choice): void
  {
    self::setAuxiliaryValue($choice);
  }

  private function getCardIdsWithDuplicateValuesInLocation(string $location): array
  {
    $cardIds = [];
    $cardsByAge = self::getCardsKeyedByValue($location);
    for ($age = 1; $age <= 11; $age++) {
      if (count($cardsByAge[$age]) >= 2) {
        foreach ($cardsByAge[$age] as $card) {
          $cardIds[] = $card['id'];
        }
      }
    }
    return $cardIds;
  }

}