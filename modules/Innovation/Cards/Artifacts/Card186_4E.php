<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card186_4E extends AbstractCard
{
  // Earhart's Lockheed Electra 10E (4th edition):
  //   - For each value below 9, junk a top card of that value from your board, in descending order.
  //     Then, if there is a junked card of each value below 9, you win.
  //   - Claim an achievement, ignoring eligibility.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setAuxiliaryValue(8); // Track next value to return
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
    
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'location_from'  => Locations::BOARD,
        'return_keyword' => true,
        'age'            => self::getAuxiliaryValue(),
      ];
    } else {
      return [
        'achieve_keyword'              => true,
        'include_special_achievements' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::decrementAuxiliaryValue() >= 0) { // Decrement the value to return next
      self::setNextStep(1);
    } else {
      $junkedValues = array_filter(self::getUniqueValues(Locations::JUNK), function($value) {
        return $value < 9;
      });
      if (count($junkedValues) === 8) {
        self::win();
      }
    }
  }

}