<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card70 extends AbstractCard
{
  // Electricity:
  // - 3rd edition:
  //   - Return all your top cards without a [INDUSTRY], then draw an [8] for each card you returned.
  // - 4th edition:
  //   - Return your top card of each color without [INDUSTRY], then draw an [8] for each card you return.

  public function getInteractionOptions(): array
  {
    return self::youMust()->return()->all()->fromYourBoard()->withoutIcon(Icons::INDUSTRY)->build();
  }

  public function afterInteraction()
  {
    for ($i = 0; $i < self::getNumChosen(); $i++) {
      self::draw(8);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::filterByIcon(self::getTopCards(), Icons::INDUSTRY)) > 0;
  }

}