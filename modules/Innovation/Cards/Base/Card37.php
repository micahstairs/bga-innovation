<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card37 extends AbstractCard
{
  // Colonialism:
  // - 3rd edition:
  //   - Draw and tuck a [3]. If it has a [PROSPERITY], repeat this dogma effect.
  // - 4th edition:
  //   - Draw and tuck a [3]. If it is green, junk all cards in the [5] deck. If it has [PROSPERITY],
  //     repeat this effect.

  public function initialExecution()
  {
    do {
      $card = self::drawAndTuck(3);
      if (self::isFourthEdition() && self::isGreen($card)) {
        self::junkBaseDeck(5);
      }
    } while (self::hasIcon($card, Icons::PROSPERITY));
  }

}