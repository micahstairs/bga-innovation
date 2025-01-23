<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card110 extends AbstractCard
{

  // Treaty of Kadesh
  // - 3rd edition:
  //   - I COMPEL you to return all top cards from your board with a demand effect!
  //   - Score a top, non-blue card from your board with a demand effect.
  // - 4th edition:
  //   - I COMPEL you to return a top card with a demand effect of each color from your board!
  //   - Score a top, non-blue card from your board with a demand effect.

  public function getInteractionOptions(): array
  {
    if (self::isCompel()) {
      return self::youMust()->all()->withDemandEffect()->fromYourBoard()->build();
    } else {
      return self::youMust()->return()->all()->non(Colors::BLUE)->fromYourBoard()->withDemandEffect()->build();
    }
  }

  public function compelMightBeEffective(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (self::hasDemandEffect($card)) {
        return true;
      }
    }
    return false;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (!self::isBlue($card) && self::hasDemandEffect($card)) {
        return true;
      }
    }
    return false;
  }

}