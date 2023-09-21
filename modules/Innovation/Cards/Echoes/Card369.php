<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card369 extends AbstractCard
{

  // Chintz
  // - 3rd edition:
  //   - Draw a [4].
  //   - If you have exactly one card in your hand, draw a [4], then draw and score a [4].
  // - 4th edition:
  //   - Draw a [4].
  //   - If you have exactly one card in your hand, draw a [4], then draw and score a [4].
  //   - If Chintz was foreseen, transfer all cards from your hand to the available achievements.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::draw(4);
    } else if (self::isSecondNonDemand()) {
      if (self::countCards('hand') === 1) {
        self::draw(4);
        self::drawAndScore(4);
      }
    } else if (self::wasForeseen()) {
      foreach (self::getCards('hand') as $card) {
        $this->game->transferCardFromTo($card, 0, 'achievements');
      }
    }
  }

}