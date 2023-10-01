<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card456 extends AbstractCard
{
  // What Does The Fox Say
  //   - If it is your turn, draw and meld an [11]. Fully execute the melded card. If any player is
  //     eligible to share an effect of the dogma, repeat this effect.

  public function hasPostExecutionLogic(): bool
  {
    return true;
  }

  public function initialExecution()
  {
    // No player shared, so do not repeat this effect.
    if (self::isPostExecution() && self::getAuxiliaryValue() === 0) {
      return;
    }

    self::setAuxiliaryValue(0); // By default, we do not want to repeat the effect
    if (self::isTheirTurn()) {
      $card = self::drawAndMeld(11);
      if (self::willShareEffect($card['dogma_icon'])) {
        self::setAuxiliaryValue(1); // Indicate that we should repeat the effect
      }
      self::fullyExecute($card);
    }
  }

  private function willShareEffect(int $icon): bool
  {
    $iconCount = self::getStandardIconCount($icon);
    foreach (self::getOtherPlayerIds() as $playerId) {
      if (self::getStandardIconCount($icon, $playerId) >= $iconCount) {
        return true;
      }
    }
    return false;
  }

}