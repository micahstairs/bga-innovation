<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card539 extends Card
{

  // Hiking:
  //   - Draw and reveal a [6]. If the top card on your board of the drawn card's color has a
  //     INDUSTRY, tuck the drawn card and draw and reveal a [7]. If the second drawn card has a
  //     HEALTH, meld it and draw an [8].

  public function initialExecution(ExecutionState $state)
  {
    $card = self::drawAndReveal(6);
    $topCard = self::getTopCardOfColor($card['color']);
    if ($this->game->hasRessource($topCard, $this->game::INDUSTRY)) {
      self::tuck($card);
      $secondCard = self::drawAndReveal(7);
      if ($this->game->hasRessource($secondCard, $this->game::HEALTH)) {
        self::meld($secondCard);
        self::draw(8);
      } else {
        self::putInHand($secondCard);
      }
    } else {
      self::putInHand($card);
    }
  }

}