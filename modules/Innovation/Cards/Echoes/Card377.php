<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card377 extends AbstractCard
{

  // Coke
  // - 3rd edition:
  //   - ECHO: Draw and tuck a [4].
  //   - Draw and reveal a [6]. If it has a [INDUSTRY], meld it and repeat this dogma effect. Otherwise, foreshadow it.
  // - 4th edition:
  //   - ECHO: Draw and tuck a [4].
  //   - Draw and reveal a [6]. If it has [INDUSTRY], meld it and repeat this effect. Otherwise, foreshadow it.
  //   - If Coke was foreseen, score your top card of each non-red color.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndTuck(4);
    } else if (self::isFirstNonDemand()) {
      while (true) {
        $card = self::drawAndReveal(6);
        if (self::hasIcon($card, Icons::INDUSTRY)) {
          self::meld($card);
        } else {
          self::foreshadow($card, [$this, 'transferToHand']);
          break;
        }
      }
    } else if (self::wasForeseen()) {
      foreach (Colors::NON_RED as $color) {
        self::score(self::getTopCardOfColor($color));
      }
    }
  }

}