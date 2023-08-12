<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Utils\Arrays;

class Card572 extends Card
{

  // Surveillance:
  //   - I DEMAND you reveal your hand! If the colors present in your hand and my hand match
  //     exactly, and your hand is not empty, I win!
  //   - Draw a [10].

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::revealHand(self::getPlayerId());
      self::revealHand(self::getLauncherId());
      $playerColors = self::getUniqueColors('hand', self::getPlayerId());
      $launcherColors = self::getUniqueColors('hand', self::getLauncherId());
      if (count($playerColors) > 0 && Arrays::isUnorderedEqual($playerColors, $launcherColors)) {
        self::win(self::getLauncherId());
      }
    } else {
      self::draw(10);
    }
  }

}