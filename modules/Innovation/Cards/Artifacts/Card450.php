<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card450 extends Card
{

  // Great Barrier Reef
  //   - Junk all cards on your board other than the top three of each color.
  //   - Splay each color on your board aslant.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $stacks = self::getCardsKeyedByColor(Locations::BOARD);
      foreach (Colors::ALL as $color) {
        for ($i = 0; $i < count($stacks[$color]) - 3; $i++) {
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