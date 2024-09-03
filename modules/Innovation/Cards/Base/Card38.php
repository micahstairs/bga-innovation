<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card38 extends AbstractCard
{
  // Gunpowder:
  // - 3rd edition:
  //   - I DEMAND you transfer a top card with a [AUTHORITY] from your board to my score pile!
  //   - If any card was transfered due to the demand, draw and score a [2].
  // - 4th edition:
  //   - I DEMAND you transfer a top card with [AUTHORITY] from your board to my score pile!
  //   - If any card was transfered due to the demand, draw and score a [2].

  public function oneTimeSetup()
  {
    self::setAuxiliaryValue(0); // Track how many cards were transfered due to the demand
  }

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::getAuxiliaryValue() >= 1) {
      self::drawAndScore(2);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->withIcon(Icons::AUTHORITY)->fromYourBoard()->toMyScore()->build();
  }

  public function handleCardChoice(array $card)
  {
    self::incrementAuxiliaryValue();
  }

  public function demandMightBeEffective(): bool
  {
    return count(self::filterByIcon(self::getTopCards(), Icons::AUTHORITY)) > 0;
  }

  public function nonDemandEffectivenessDependsOnDemand(): bool
  {
    return true;
  }

}