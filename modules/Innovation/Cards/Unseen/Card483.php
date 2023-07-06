<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card483 extends Card
{

  // Assassination:
  //   - I DEMAND you draw and reveal a [1]! If it has a [AUTHORITY], transfer it and the top card
  //     on your board of its color to my score pile!
  //   - If no player has a top green card, claim the Confidence achievement.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $card = self::drawAndReveal(1);
      if (self::hasIcon($card, $this->game::AUTHORITY)) {
        self::transferToScorePile($card, self::getLauncherId());
        self::transferToScorePile(self::getTopCardOfColor($card['color']), self::getLauncherId());
      } else {
        self::putInHand($card);
      }
    } else {
      $isGreenCardOnAnyBoard = false;
      foreach ($this->game->getAllActivePlayerIds() as $player) {
          if (self::getTopCardOfColor($this->game::GREEN, $player)) {
            $isGreenCardOnAnyBoard = true;
            break;
          }
      }
      if (!$isGreenCardOnAnyBoard) {
        $this->game->claimSpecialAchievement(self::getPlayerId(), 595);
      }
    }
  }

}