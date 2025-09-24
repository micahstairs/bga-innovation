<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card378 extends AbstractCard
{

  // Octant
  // - 3rd edition:
  //   - I DEMAND you transfer a top non-red card with a [HEALTH] or [INDUSTRY] from your board to
  //     my board! If you do, draw and foreshadow a [6]!
  //   - Draw and foreshadow a [6].
  // - 4th edition:
  //   - I DEMAND you transfer a top card with [HEALTH] or [INDUSTRY] of each non-red color from your
  //     board to mine! If you transfer at least one, and Octant wasn't foreseen, draw and foreshadow
  //     a [6]!
  //   - Draw and foreshadow a [6].

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      self::drawAndForeshadow(6);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return self::youMust()->withColor(Colors::NON_RED)->withIcons([Icons::HEALTH, Icons::INDUSTRY])->fromYourBoard()->toMine()->build();
    } else {
      return self::youMust()->all()->withColor(Colors::NON_RED)->withIcons([Icons::HEALTH, Icons::INDUSTRY])->fromYourBoard()->toMine()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0 && self::isFourthEdition() && !self::wasForeseen()) {
      self::drawAndForeshadow(6);
    }
  }

}