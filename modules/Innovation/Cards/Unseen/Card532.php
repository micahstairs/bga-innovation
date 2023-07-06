<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card532 extends Card
{

  // Museum:
  //   - If you have a [2] in your score pile, draw a [6].
  //   - If you have a [1] in your score pile, draw a [7]. Otherwise, draw a [5].

  public function initialExecution()
  {
    $cardCounts = $this->game->countCardsInLocationKeyedByAge(self::getPlayerId(), 'score');
    if (self::getEffectNumber() == 1) {
      if ($cardCounts[2] > 0) {
        self::draw(6);
      }
    } else {
      if ($cardCounts[1] > 0) {
        self::draw(7);
      } else {
        self::draw(5);
      }
    }
  }

}