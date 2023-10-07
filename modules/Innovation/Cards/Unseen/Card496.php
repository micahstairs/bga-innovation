<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;

class Card496 extends AbstractCard
{
  // Meteorology
  //   - Draw and reveal a [3]. If it has a [HEALTH], score it. Otherwise, if it has a
  //     [PROSPERITY], return it and draw two [3]. Otherwise, tuck it.
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
      if (self::getAllIconCounts()[Icons::AUTHORITY] == 0) {
        $this->game->claimSpecialAchievement(self::getPlayerId(), CardIds::ZEN);
      }
    }
  }

}