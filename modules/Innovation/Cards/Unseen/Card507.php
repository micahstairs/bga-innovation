<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card507 extends AbstractCard
{

  // Knights Templar
  //   - I DEMAND you unsplay a splayed color on your board! If you do, transfer the top card on
  //     your board of that color to my score pile!
  //   - You may splay your red or green cards left.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->unsplay()->currentlySplayed();
    } else {
      return self::youMay()->splayLeft([Colors::RED, Colors::GREEN]);
    }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      if (self::getNumChosen() > 0) {
        $card = self::getTopCardOfColor(self::getLastSelectedColor());
        self::transferToScorePile($card, self::getLauncherId());
      }
    }
  }
}