<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card99_3E extends AbstractCard
{
  // Databases (3rd edition):
  //   - I DEMAND you return half (rounded up) of the cards in your score pile!

  public function getInteractionOptions(): InteractionBuilder
  {
    $numCards = ceil(self::countCards(Locations::SCORE) / 2);
    return self::youMust()->return()->exactly($numCards)->fromYourScore();
  }

  public function demandMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

}