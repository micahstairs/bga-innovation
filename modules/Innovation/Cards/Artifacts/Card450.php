<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card450 extends AbstractCard
{

  // Great Barrier Reef
  //   - Junk all cards on your board other than the top five of each color.
  //   - Splay each color on your board aslant.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $stacks = self::getCardsKeyedByColor(Locations::BOARD);
      foreach (Colors::ALL as $color) {
        for ($i = 0; $i < count($stacks[$color]) - 5; $i++) {
          self::junk($stacks[$color][$i]);
        }
      }
    } else if (self::isSecondNonDemand()) {
      foreach (Colors::ALL as $color) {
        self::splayAslant($color);
      }
    }
  }

}