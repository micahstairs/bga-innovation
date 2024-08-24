<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card20 extends AbstractCard
{
  // Mapmaking:
  // - 3rd edition:
  //   - I DEMAND you transfer a [1] from your score pile, if it has any, to my score pile!
  //   - If any card was transferred due to the demand, draw and score a [1]!
  // - 4th edition:
  //   - I DEMAND you transfer a [1] from your score pile to my score pile!
  //   - If any card was transferred due to the demand, draw and score a [1]!

  public function oneTimeSetup()
  {
    self::setAuxiliaryValue(0); // Used to track how many cards were transferred due to the demand
  }

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::getAuxiliaryValue() > 0) {
      self::drawAndScore(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->value(1)->fromScore()->toMine()->build();
  }

  public function handleCardChoice(array $card)
  {
    self::incrementAuxiliaryValue();
  }

  public function demandMightBeEffective(): bool
  {
    return self::countCardsKeyedByValue(Locations::SCORE)[1] > 0;
  }

}