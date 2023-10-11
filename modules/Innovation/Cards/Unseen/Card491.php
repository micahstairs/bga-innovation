<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card491 extends AbstractCard
{
  // Woodworking
  //   - Draw and meld a [2]. If the melded card is a bottom card on your board, score it.

  public function initialExecution()
  {
    $card = self::drawAndMeld(2);
    if ($card['position'] == 0) {
      self::score($card);
    }
  }

}