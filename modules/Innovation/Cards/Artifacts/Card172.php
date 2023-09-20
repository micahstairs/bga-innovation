<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card172 extends Card
{
  // Pride and Prejudice
  //   - Draw and meld a [6]. If the drawn card's color is the color with the fewest (or tied)
  //     number of visible cards on your board, score the melded card, and repeat this effect.

  public function initialExecution()
  {
      do {
          $card = self::drawAndMeld(6);
          $numCards = self::countVisibleCardsInStack($card['color']);
          foreach (Colors::ALL as $color) {
              if ($numCards > self::countVisibleCardsInStack($color)) {
                  return;
              }
          }
          self::score($card);
      } while (true);
  }

}