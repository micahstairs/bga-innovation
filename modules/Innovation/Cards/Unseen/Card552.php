<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card552 extends Card
{

  // Subway
  //   - Draw and tuck a [7]. If you have seven visible cards on your board of the color of the
  //     tucked card, draw a [9]. Otherwise, junk all cards on your board of that color, and
  //     draw an [8].

  public function initialExecution()
  {
    $card = self::drawAndTuck(7);
    $pile = self::getCardsKeyedByColor('board');
    if ($this->game->countVisibleCards(self::getPlayerId(), $card['color']) >= 7) {
      self::draw(9);
    } else {
      foreach ($pile[$card['color']] as $card) {
        self::junk($card);
      }
      self::draw(8);
    }
  }

}