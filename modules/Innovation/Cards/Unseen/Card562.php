<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card562 extends AbstractCard
{

  // Shangri-La:
  //   - Draw and tuck an [8]. If it has a [INDUSTRY], score it. Otherwise, draw and meld an [8].
  //     If you do, repeat this effect.

  public function initialExecution()
  {
    while (true) {
      $card = self::drawAndTuck(8);
      if (self::hasIcon($card, Icons::INDUSTRY)) {
        self::score($card);
        break;
      } else if (self::drawAndMeld(8)['faceup_age'] != 8) {
        break;
      }
    }
  }

}