<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card86 extends AbstractCard
{
  // Genetics:
  // - 3rd edition:
  //   - Draw and meld a [10]. Score all cards beneath it.
  // - 4th edition:
  //   - Draw and meld an [11]. Score all cards beneath it.

  public function initialExecution()
  {
    $value = self::isFourthEdition() ? 11 : 10;
    $meldedCard = self::drawAndMeld($value);
    foreach (self::getStack(self::getColor($meldedCard)) as $card) {
      if (self::getId($card) != self::getId($meldedCard)) {
        self::score($card);
      }
    }
  }

}
