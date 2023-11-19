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
      return [
        'location' => Locations::SAFE,
        'owner_to' => self::getLauncherId(),
        'age'      => self::getMaxValueInLocation(Locations::SAFE),
      ];
    } else if (self::isThirdInteraction() || self::isFourthInteraction()) {
      return [
        'location' => Locations::SCORE,
        'owner_to' => self::getLauncherId(),
        'age'      => self::getMaxValueInLocation(Locations::SCORE),
      ];
    } else {
      return [
        'location_from' => Locations::SCORE,
        'meld_keyword'  => true,
        'age'           => self::getMinValueInLocation(Locations::SCORE),
      ];
    }
  }

}