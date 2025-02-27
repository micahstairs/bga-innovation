<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card40 extends AbstractCard
{
  // Navigation:
  // - 3rd edition:
  //   - I DEMAND you transfer a [2] or [3] from your score pile, if it has any, to my score pile!
  // - 4th edition:
  //   - I DEMAND you transfer a [2] or [3] from your score pile to my score pile!

  public function getInteractionOptions(): array
  {
    return self::youMust()->range(2, 3)->fromYourScore()->toMine()->build();
  }

  public function demandMightBeEffective(): bool
  {
    return count(self::filterByValue(self::getCards(Locations::SCORE), [2, 3])) > 0;
  }

}