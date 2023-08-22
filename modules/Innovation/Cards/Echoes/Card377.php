<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card377 extends Card
{

  // Coke
  // - 3rd edition:
  //   - ECHO: Draw and tuck a [4].
  //   - Draw and reveal a [6]. If it has a [INDUSTRY], meld it and repeat this dogma effect. Otherwise, foreshadow it.
  // - 4th edition:
  //   - ECHO: Draw and tuck a [4].
  //   - Draw and reveal a [6]. If it has a [INDUSTRY], meld it and repeat this effect. Otherwise, foreshadow it.
  //   - If Coke was foreseen, score your top card of each non-red color.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(4);
    } else if (self::isFirstNonDemand()) {
      while (true) {
        $card = self::drawAndReveal(6);
        if (self::hasIcon($card, $this->game::INDUSTRY)) {
          self::meld($card);
        } else {
          if (!self::foreshadow($card)) {
            self::transferToHand($card);
          }
          break;
        }
      }
    } else if (self::wasForeseen()) {
      foreach (self::getAllColorsOtherThan($this->game::RED) as $color) {
        self::score(self::getTopCardOfColor($color));
      }
    }
  }

}