<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card483 extends AbstractCard
{

  // Assassination:
  //   - I DEMAND you draw and reveal a [1]! If it has [AUTHORITY], transfer it and the top card
  //     on your board of its color to my score pile!
  //   - If no player has a top green card, claim the Confidence achievement.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $card = self::drawAndReveal(1);
      if (self::hasIcon($card, Icons::AUTHORITY)) {
        self::transferToScorePile($card, self::getLauncherId());
        self::transferToScorePile(self::getTopCardOfColor($card['color']), self::getLauncherId());
      } else {
        self::transferToHand($card);
      }
    } else {
      $isGreenCardOnAnyBoard = false;
      foreach (self::getPlayerIds() as $player) {
          if (self::getTopCardOfColor(Colors::GREEN, $player)) {
            $isGreenCardOnAnyBoard = true;
            break;
          }
      }
      if (!$isGreenCardOnAnyBoard) {
        self::claim(CardIds::CONFIDENCE);
      }
    }
  }

}