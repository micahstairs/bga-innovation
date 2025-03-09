<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;

class Card53 extends AbstractCard
{
  // Astronomy:
  // - 3rd edition:
  //   - Draw and reveal a [6]. If the card is green or blue, meld it and repeat this dogma effect.
  //   - If all non-purple top cards on your board are value [6] or higher, claim the Universe achievement.
  // - 4th edition:
  //   - Draw and reveal a [6]. If the card is green or blue, meld it and repeat this effect.
  //   - If all non-purple top cards on your board are value [6] or higher, claim the Universe achievement.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      while (true) {
        $card = self::drawAndReveal(6);
        $color = self::getColor($card);
        $this->notifications->notifyCardColor($color);
        if (self::isGreen($card) || self::isBlue($card)) {
          self::meld($card);
        } else {
          self::transferToHand($card);
          return;
        }
      }
    } else if (self::isSecondNonDemand()) {
      if (self::isEligibleForUniverseAchievement()) {
        self::claim(CardIds::UNIVERSE);
      }
    }
  }

  private function isEligibleForUniverseAchievement(): bool
  {
    foreach (self::getTopCards() as $card) {
      if (!self::isPurple($card) && self::getValue($card) < 6) {
        return false;
      }
    }
    return true;
  }

}