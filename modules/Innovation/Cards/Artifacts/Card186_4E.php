<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card186_4E extends AbstractCard
{
  // Earhart's Lockheed Electra 10E (4th edition):
  //   - For each value below 9, junk a top card of that value from your board, in descending order.
  //     Then, if there is a junked card of each value below 9, you win.
  //   - Claim an available achievement, ignoring eligibility.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setAuxiliaryValue(8); // Track next value to return
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }

  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->junk()->value(self::getAuxiliaryValue())->fromYourBoard();
    } else {
      return self::youMust()->achieve()->includingSpecialAchievements();
    }
  }

  public function afterInteraction()
  {
    if (self::decrementAuxiliaryValue() >= 0) { // Decrement the value to return next
      self::setNextStep(1);
    } else {
      $junkedValues = array_filter(self::getUniqueValuesInLocation(Locations::JUNK), function ($value) {
        return $value < 9;
      });
      if (count($junkedValues) === 8) {
        self::win();
      }
    }
  }

}