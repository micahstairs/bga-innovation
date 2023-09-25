<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;

class Card192_4E extends AbstractCard
{
  // Action Comics #1 (4th edition):
  //   - I COMPEL you to draw an [8]! If it is green, achieve Action Comics #1 if it is a top card!
  //     Otherwise, if it has a [EFFICIENCY], return it, and if your top card of its color has a
  //     [EFFICIENCY], transfer it to my achievements, and repeat this effect!

  public function initialExecution()
  {
    do {
      $repeat = false;
      $card = self::draw(8);
      if (self::isGreen($card)) {
        self::achieve($this->game->getIfTopCardOnBoard(CardIds::ACTION_COMICS));
      } else if (self::hasIcon($card, Icons::EFFICIENCY)) {
        self::return($card);
        $topCard = self::getTopCardOfColor($card['color']);
        if (self::hasIcon($topCard, Icons::EFFICIENCY)) {
          self::transferToAchievements($topCard, self::getLauncherId());
          $repeat = true;
        }
      }
    } while ($repeat);
  }

}