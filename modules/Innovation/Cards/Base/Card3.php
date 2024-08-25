<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card3 extends AbstractCard
{

  // Archery:
  // - 3rd edition:
  //   - I DEMAND you draw a [1], then transfer the highest card in your hand to my hand!
  // - 4th edition:
  //   - I DEMAND you draw a [1], then transfer the highest card in your hand to my hand!
  //   - Junk an available achievement of value [1] or [2].

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::draw(1);
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return self::youMust()->highest()->fromYourHand()->toMine()->build();
    } else {
      return self::youMust()->junk()->range(1, 2)->fromAvailableAchievements()->build();
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::filterByValue(self::getAvailableStandardAchievements(), [1, 2])) > 0;
  }

}