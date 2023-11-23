<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card4 extends AbstractCard
{

  // Metalworking:
  // - 3rd edition:
  //   - Draw and reveal a [1]. If it has [AUTHORITY], score it and repeat this effect.
  // - 4th edition:
  //   - Draw and reveal a [1]. If it has [AUTHORITY], score it and repeat this effect.

  public function initialExecution()
  {
    while (true) {
      $card = self::drawAndReveal(1);
      if (self::hasIcon($card, Icons::AUTHORITY)) {
        $this->notifications->notifyPresenceOfIcon(Icons::AUTHORITY);
        self::score($card);
      } else {
        $this->notifications->notifyAbsenceOfIcon(Icons::AUTHORITY);
        self::transferToHand($card);
        return;
      }
    }
    
  }
}