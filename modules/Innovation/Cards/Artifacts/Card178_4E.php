<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card178_4E extends AbstractCard
{
  // Jedlik's Electromagnetic Self-Rotor (4th edition):
  //   - Draw and score an [8]. 
  //   - Draw and meld an [8]. If it is an [8], choose a value, and junk all cards in the deck of that value.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::drawAndScore(8);
    } else if (self::isSecondNonDemand()) {
      $card = self::drawAndMeld(8);
      if ($card['faceup_age'] == 8) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return ['choose_value' => true];
  }

  public function handleValueChoice(int $value) {
    self::junkBaseDeck($value);
  }

}