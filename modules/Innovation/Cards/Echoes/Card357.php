<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card357 extends AbstractCard
{

  // Liquid Fire
  //   - I DEMAND you draw a card of value equal to the highest bonus on your board! Transfer it to
  //     my forecast! If it is red, transfer all cards from your hand to my score pile!

  public function initialExecution()
  {
    $bonuses = self::getBonuses();
    $maxBonus = $bonuses ? max($bonuses) : 0;
    $card = self::drawAndReveal($maxBonus);
    self::transferToForecast($card, [$this, 'transferToHand'], self::getLauncherId());
    if (self::isRed($card)) {
      foreach (self::getCards('hand') as $card) {
        self::transferToScorePile($card, self::getLauncherId());
      }
    }
  }

}