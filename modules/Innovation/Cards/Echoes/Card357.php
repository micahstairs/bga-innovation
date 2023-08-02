<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card357 extends Card
{

  // Liquid Fire
  //   - I DEMAND you draw a card of value equal to the highest bonus on your board! Transfer it to
  //     my forecast! If it is red, transfer all cards from your hand to my score pile!

  public function initialExecution()
  {
    $maxBonus = $this->game->getMaxBonusIconOnBoard(self::getPlayerId());
    $card = self::drawAndReveal($maxBonus);
    self::foreshadow($card, self::getLauncherId());
    if (self::isRed($card)) {
      foreach (self::getCards('hand') as $card) {
        self::transferToScorePile($card, self::getLauncherId());
      }
    }
  }

}