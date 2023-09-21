<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card575 extends AbstractCard
{

  // Hacking:
  //   - I DEMAND you transfer your two highest secrets to my safe! Transfer all the highest cards
  //     in your score pile to my score pile! Meld all the lowest cards from your score pile!

  public function initialExecution()
  {
    self::setMaxSteps(4);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction() || self::isSecondInteraction()) {
      return [
        'location_from' => 'safe',
        'location_to'   => 'safe',
        'owner_to'      => self::getLauncherId(),
        'age'           => self::getMaxValueInLocation('safe'),
      ];
    } else if (self::isThirdInteraction()) {
      return [
        'n'             => 'all',
        'location_from' => 'score',
        'location_to'   => 'score',
        'owner_to'      => self::getLauncherId(),
        'age'           => self::getMaxValueInLocation('score'),
      ];
    } else {
      return [
        'n'             => 'all',
        'location_from' => 'score',
        'meld_keyword'  => true,
        'age'           => self::getMinValueInLocation('score'),
      ];
    }
  }

}