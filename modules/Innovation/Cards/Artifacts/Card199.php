<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card199 extends AbstractCard
{
  // Philips Compact Cassette
  //   - I COMPEL you to unsplay all splayed colors on your board!
  //   - Splay up two colors on your board.

  public function initialExecution()
  {
    if (self::isCompel()) {
      foreach (Colors::ALL as $color) {
        self::unsplay($color);
      }
    } else if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->splayUp()->exactly(2)->build();
  }

  public function compelMightBeEffective(): bool
  {
    return self::countSplayedColors() > 0;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    foreach (Colors::ALL as $color) {
      if (self::canSplayUp($color)) {
        return true;
      }
    }
    return false;
  }

}