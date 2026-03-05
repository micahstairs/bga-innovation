<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card178_4E extends AbstractCard
{
  // Jedlik's Electromagnetic Self-Rotor (4th edition):
  //   - Draw and score an [8]. 
  //   - Draw and meld an [8]. If you do, choose a value, and junk all cards in the deck of that value.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::drawAndScore(8);
    } else if (self::isSecondNonDemand()) {
      $card = self::drawAndMeld(8);
      if (self::getFaceupValue($card) == 8) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->chooseValue();
  }

  public function handleValueChoice(int $value)
  {
    self::junkBaseDeck($value);
  }

}