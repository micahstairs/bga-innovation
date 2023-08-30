<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card111 extends Card
{

  // Sibidu Needle
  //   - Draw and reveal a [1]. If you have a top card of matching color and value to the drawn
  //     card, score the drawn card and repeat this effect.

  public function initialExecution()
  {
    while (true) {
      $card = self::drawAndReveal(1);
      $topCard = self::getTopCardOfColor($card['color']);
      if ($topCard && $card['faceup_age'] == $topCard['faceup_age']) {
        self::score($card);
      } else {
        break;
      }
    }
    self::transferToHand($card);
  }

}