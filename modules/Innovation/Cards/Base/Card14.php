<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card14 extends AbstractCard
{
  // Mysticism:
  //   - Draw and reveal a [1]. If it is the same color as any card on your board, meld it and draw a [1].

  public function initialExecution()
  {
    $card = self::drawAndReveal(1);
    $color = self::getColor($card);
    $this->notifications->notifyCardColor($color);
    if (self::getTopCardOfColor($color)) {
      self::meld($card);
      self::draw(1);
    } else {
      self::transferToHand($card);
    }
  }

}