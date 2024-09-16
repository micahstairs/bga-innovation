<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card99_4E extends AbstractCard
{
  // Databases (4th edition):
  //   - I DEMAND you return a number of cards from your score pile equal to the value of your
  //     highest achievement!

  public function getInteractionOptions(): array
  {
    $numCards = self::getMaxValueInLocation(Locations::ACHIEVEMENTS);
    return self::youMust()->return()->exactly($numCards)->fromYourScore()->build();
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

}