<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card394 extends AbstractCard
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
      self::setAuxiliaryValue(self::getColor($card));
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayRight(self::getAuxiliaryValue());
    } else {
      $count = self::getStandardIconCount(Icons::CONCEPT);
      self::setAuxiliaryValue($count); // Store the number of [CONCEPT] icons on the board
      return self::youMust()->junk()->value($count)->fromAvailableAchievements();
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
      if (self::isValuedCard($card) && self::getValue($card) < $value) {
        self::junk($card);
      }
    }
  }

}