<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card552 extends Card
{

  // Subway
  //   - Draw and tuck a [7]. If you have seven or more visible cards on your board of the color of
  //     the tucked card, draw a [9]. Otherwise, junk all cards on your board of that color, and
  //     draw an [8].

  public function initialExecution()
  {
    $card = self::drawAndTuck(7);
    $pile = $this->game->getCardsInLocationKeyedByColor(self::getPlayerId(), 'board');
    if (count($pile[$card['color']]) >= 7) {
      self::draw(9);
    } else {
      foreach ($pile[$card['color']] as $card) {
        self::junk($card);
      }
      self::draw(8);
    }
  }

}