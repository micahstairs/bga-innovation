<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card186_3E extends AbstractCard
{
  // Earhart's Lockheed Electra 10E (3rd edition):
  //   - For each value below nine, return a top card of that value from your board, in descending
  //     order. If you return eight cards, you win. Otherwise, claim an achievement, ignoring
  //     eligibility.

  public function initialExecution()
  {
    self::setAuxiliaryValue(8); // Track next value to return
    self::setAuxiliaryValue2(0); // Track number of cards returned
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->value(self::getAuxiliaryValue())->fromYourBoard();
    } else {
      return self::youMust()->achieve()->includingSpecialAchievements();
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 1) {
      self::incrementAuxiliaryValue2(1); // Increment number of cards returned
    }
    if (self::decrementAuxiliaryValue() >= 0) { // Decrement the value to return next
      self::setNextStep(1);
    } else if (self::getAuxiliaryValue2() >= 8) {
      self::win();
    } else {
      self::setMaxSteps(2);
    }
  }

}