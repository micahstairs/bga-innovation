<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card56_4E extends AbstractCard
{
  // Encyclopedia (4th edition):
  //   - Choose a value. You may meld all the cards of that value in your score pile.
  //   - You may junk an available achievement of value [5], [6], or [7].

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      if (self::isFirstInteraction()) {
        return ['choose_value' => true];
      } else {
        return [
          'can_pass'      => true,
          'n'             => 'all',
          'location_from' => Locations::SCORE,
          'meld_keyword'  => true,
          'age'           => self::getAuxiliaryValue(),
        ];
      }
    } else {
      return [
        'can_pass'      => true,
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'junk_keyword' => true,
        'age_min'       => 5,
        'age_max'       => 7,
      ];
    }
  }

  public function handleValueChoice(int $value)
  {
    self::setAuxiliaryValue($value);
  }

}
