<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card48 extends AbstractCard
{
  // The Pirate Code:
  // - 3rd edition:
  //   - I DEMAND you transfer two cards of value [4] or less from your score pile to my score pile!
  //   - If any cards were transferred due to the demand, score the lowest top card with a [PROSPERITY] from your board.
  // - 4th edition:
  //   - I DEMAND you transfer two cards of value [4] or less from your score pile to my score pile!
  //   - If any cards were transferred due to the demand, score the lowest top card with [PROSPERITY] from your board.

  public function oneTimeSetup()
  {
    self::setAuxiliaryValue(0); // Track how many cards were transferred due to the demand
  }

  public function initialExecution()
  {
    if (self::isDemand() || self::getAuxiliaryValue() >= 1) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return self::youMust()->exactly(2)->maxValue(4)->fromYourScore()->toMine()->build();
    } else {
      return self::youMust()->score()->lowest()->withIcon(Icons::PROSPERITY)->fromYourBoard()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isDemand()) {
      self::incrementAuxiliaryValue();
    }
  }

  public function demandMightBeEffective(): bool
  {
    return count(self::filterByValue(self::getCards(Locations::SCORE), [0, 1, 2, 3, 4])) > 0;
  }

  public function nonDemandEffectivenessDependsOnDemand(): bool
  {
    return true;
  }

}