<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card111 extends AbstractCard
{

  // Sibidu Needle
  //   - Draw and reveal a [1]. If you have a top card of matching color and value to the drawn
  //     card, score the drawn card and repeat this effect.

  public function initialExecution()
  {
    while (true) {
      $card = self::drawAndReveal(1);
      $topCard = self::getTopCardOfColor(self::getColor($card));
      if ($topCard && self::getFaceupValue($card) == self::getFaceupValue($topCard)) {
        self::score($card);
      } else {
        break;
      }
    }
    self::transferToHand($card);
  }

}