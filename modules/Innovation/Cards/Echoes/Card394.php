<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card394 extends Card
{

  // Kaleidoscope
  // - 3rd edition
  //   - Draw and meld a [7]. You may splay your cards of that color right.
  // - 4th edition
  //   - Draw and meld a [7]. You may splay your cards of that color right.
  //   - Junk an available achievement of value equal to the number of [CONCEPT] on your board.
  //     If Kaleidoscope was foreseen, junk all available achievements of lower value.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $card = self::drawAndMeld(7);
      self::setAuxiliaryValue($card['color']);
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [self::getAuxiliaryValue()],
      ];
    } else {
      $count = self::getStandardIconCount(Icons::CONCEPT);
      self::setAuxiliaryValue($count); // Store the number of [CONCEPT] icons on the board
      return [
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword'  => true,
        'age'           => $count,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondNonDemand() && self::wasForeseen()) {
      self::junkAchievementsOfLowerValue(self::getAuxiliaryValue());
    }
  }

  public function junkAchievementsOfLowerValue($value)
  {
    foreach (self::getCards(Locations::AVAILABLE_ACHIEVEMENTS) as $card) {
      if (self::isValuedCard($card) && $card['age'] < $value) {
        self::junk($card);
      }
    }
  }

}