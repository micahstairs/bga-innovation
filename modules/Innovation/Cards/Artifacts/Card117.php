<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card117 extends Card
{

  // Electrum Stater of Efesos
  //   - Draw and reveal a [3]. If you do not have a top card of the drawn card's color, meld it
  //     and repeat this effect.

  public function initialExecution()
  {
    while (true) {
      $card = self::drawAndReveal(3);
      if (self::getTopCardOfColor($card['color'])) {
        self::transferToHand($card);
        return;
      } else {
        self::meld($card);
      }
    }
  }

}