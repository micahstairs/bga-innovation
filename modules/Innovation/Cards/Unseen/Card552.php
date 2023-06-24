<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card552 extends Card
{

  // Subway
  //   - Draw and tuck a [7]. If you have seven or more visible 
  //     cards on your board of the color of the tucked card, draw a 
  //     [9]. Otherwise, junk all cards on your board of that color, 
  //     and draw an [8].

  public function initialExecution()
  {
    // "Draw and tuck a [7]"
    $card = self::drawAndTuck(7);
    // "If you have seven or more visible cards on your board of the color of the tucked card"
    $pile_cards = $this->game->getCardsInLocationKeyedByColor(self::getPlayerId(), 'board');
    if (count($pile_cards[$card['color']]) >= 7) {
        // "draw a [9]"
        self::draw(9);
    } else { // "Otherwise,"
        // "junk all cards on your board of that color"
        foreach ($pile_cards[$card['color']] as $card) {
            $this->game->junkCard($card);
        }
        // "and draw an [8]."
        self::draw(8);
    }
  }

  public function getInteractionOptions(): array
  {
  }

  public function afterInteraction()
  {
  }
  
}