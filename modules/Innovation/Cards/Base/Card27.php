<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card27 extends AbstractCard
{
  // Engineering:
  // - 3rd edition:
  //   - I DEMAND you transfer all top cards with a [AUTHORITY] from your board to my score pile!
  //   - You may splay your red cards left.
  // - 4th edition:
  //   - I DEMAND you transfer a top card with [AUTHORITY] of each color from your board to my score pile!
  //   - You may splay your red cards left.

  public function initialExecution()
  {
    if (self::isDemand()) {
      foreach (self::getTopCards() as $card) {
        if (self::hasIcon($card, Icons::AUTHORITY)) {
          self::transferToScorePile($card, self::getLauncherId());
        }
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->splayLeft()->withColor([Colors::RED])->build();
  }

  public function demandMightBeEffective(): bool
  {
    return count(self::filterByIcon(self::getTopCards(), Icons::AUTHORITY)) > 0;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::canSplayLeft([Colors::RED]);
  }

}