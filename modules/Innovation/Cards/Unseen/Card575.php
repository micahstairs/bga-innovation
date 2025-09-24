<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card575 extends AbstractCard
{

  // Hacking:
  //   - I DEMAND you transfer your two highest secrets to my safe! Transfer the two highest cards
  //     in your score pile to my score pile! Meld the two lowest cards from your score pile!

  public function initialExecution()
  {
    self::setMaxSteps(6);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction() || self::isSecondInteraction()) {
      $value = self::getMaxValueInLocation(Locations::SAFE);
      return self::youMust()->value($value)->fromYourSafe()->toMine()->build();
    } else if (self::isThirdInteraction() || self::isFourthInteraction()) {
      $value = self::getMaxValueInLocation(Locations::SCORE);
      return self::youMust()->value($value)->fromYourScore()->toMine()->build();
    } else {
      $value = self::getMinValueInLocation(Locations::SCORE);
      return self::youMust()->meld()->value($value)->fromYourScore()->build();
    }
  }

}