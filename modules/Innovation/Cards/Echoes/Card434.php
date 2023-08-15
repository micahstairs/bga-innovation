<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card434 extends Card
{

  // Sudoku
  // - 3rd edition 
  //   - Draw and meld a card of any value. If you have at least nine different bonus values
  //     visible on your board, you win. Execute each of the melded card's non-demand dogma
  //     effects. Do not share them.
  // - 4th edition
  //   - Draw and meld a card of any value. If you have nine different bonus values visible
  //     on your board, you win. Otherwise, self-execute the melded card.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return ['choose_value' => true];
  }

  public function handleSpecialChoice($value) {
    $card = self::drawAndMeld($value);
    if (count(array_unique(self::getBonuses())) >= 9) {
      self::win();
    } else {
      self::selfExecute($card);
    }
  }

}