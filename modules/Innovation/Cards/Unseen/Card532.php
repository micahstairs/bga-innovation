<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card532 extends AbstractCard
{

  // Gallery:
  //   - If you have a [2] in your score pile, draw a [6].
  //   - If you have a [1] in your score pile, draw a [7]. Otherwise, draw a [5].

  public function initialExecution()
  {
    $cardCounts = self::countCardsKeyedByValue(Locations::SCORE);
    if (self::isFirstNonDemand()) {
      if ($cardCounts[2] > 0) {
        self::draw(6);
      }
    } else if (self::isSecondNonDemand()) {
      if ($cardCounts[1] > 0) {
        self::draw(7);
      } else {
        self::draw(5);
      }
    }
  }

}