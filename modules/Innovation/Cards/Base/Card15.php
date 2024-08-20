<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card15 extends AbstractCard
{
  // Calendar:
  //   - If you have more cards in your score pile than in your hand, draw two [3].

  public function initialExecution()
  {
    if (self::countCards(Locations::SCORE) > self::countCards(Locations::HAND)) {
      self::draw(3);
      self::draw(3);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::countCards(Locations::SCORE) > self::countCards(Locations::HAND);
  }

}