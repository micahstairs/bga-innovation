<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Icons;

class Card496 extends AbstractCard
{
  // Meteorology
  //   - Draw and reveal a [3]. If it has [HEALTH], score it. Otherwise, if it has [PROSPERITY],
  //     return it and draw two [3]. Otherwise, tuck it.
  //   - If you have no [AUTHORITY], claim the Zen achievement.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $card = self::drawAndReveal(3);
      if (self::hasIcon($card, Icons::HEALTH)) {
        self::score($card);
      } else if (self::hasIcon($card, Icons::PROSPERITY)) {
        self::return($card);
        self::draw(3);
        self::draw(3);
      } else {
        self::tuck($card);
      }
    } else if (self::isSecondNonDemand()) {
      if (self::getStandardIconCounts()[Icons::AUTHORITY] == 0) {
        self::claim(CardIds::ZEN);
      }
    }
  }

}