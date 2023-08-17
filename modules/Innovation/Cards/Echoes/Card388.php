<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card388 extends Card
{

  // Shrapnel
  // - 3rd edition
  //   - I DEMAND you draw and tuck a [6]! Transfer the top two cards of its color from your board
  //     to my score pile! Transfer the bottom card of its color from my board to your score pile!
  // - 4th edition
  //   - I DEMAND you draw and tuck a [6]! Transfer your top two cards of its color to my score
  //     score pile! Score the bottom card of its color on my board!

  public function initialExecution()
  {
    $tuckedCard = self::drawAndTuck(6);
    self::transferToScorePile(self::getTopCardOfColor($tuckedCard['color']), self::getLauncherId());
    self::transferToScorePile(self::getTopCardOfColor($tuckedCard['color']), self::getLauncherId());
    if (self::isFirstOrThirdEdition()) {
      self::transferToScorePile(self::getBottomCardOfColor($tuckedCard['color'], self::getLauncherId()));
    } else {
      self::score(self::getBottomCardOfColor($tuckedCard['color'], self::getLauncherId()));
    }
  }

}
