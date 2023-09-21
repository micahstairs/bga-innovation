<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card142 extends AbstractCard
{

  // Along the River during the Qingming Festival
  //   - Draw and reveal a [4]. If it is yellow, tuck it. If it is purple, score it. Otherwise,
  //     repeat this effect.

  public function initialExecution()
  {
    while (true) {
      $card = self::drawAndReveal(4);
      if (self::isYellow($card)) {
        self::tuck($card);
        break;
      } else if (self::isPurple($card)) {
        self::score($card);
        break;
      } else {
        self::transferToHand($card);
      }
    }
  }

}