<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card78 extends AbstractCard
{
  // Mobility:
  // - 3rd edition:
  //   - I demand you transfer the two highest non-red top cards without a [INDUSTRY] from your
  //     board to my score pile! If you transferred any cards, draw an 8.
  // - 4th edition:
  //   - I demand you transfer your two highest non-red top cards without [INDUSTRY] of different
  //     colors to my score pile! If you transfer any cards, draw an 8.

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->exactly(2)->highest()->non(Colors::RED)->withoutIcon(Icons::INDUSTRY)->fromYourBoard()->toMyScore()->refreshingSelection();
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      self::draw(8);
    }
  }

  public function demandMightBeEffective(): bool
  {
    $topNonRedCards = self::filterByColor(self::getTopCards(), Colors::NON_RED);
    return count(self::filterWithoutIcon($topNonRedCards, Icons::INDUSTRY)) > 0;
  }

}