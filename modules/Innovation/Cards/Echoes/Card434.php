<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card434 extends AbstractCard
{

  // Sudoku
  // - 3rd edition 
  //   - Draw and meld a card of any value. If you have at least nine different bonus values
  //     visible on your board, you win. Execute each of the melded card's non-demand dogma
  //     effects. Do not share them.
  // - 4th edition
  //   - ECHO: You may tuck any number of cards from your hand.
  //   - Draw and meld a card of any value. If you have nine different bonus values on your
  //     board, you win. Otherwise, self-execute the melded card.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isEcho()) {
      return self::youMay()->tuck()->anyNumber()->fromYourHand();
    } else {
      return self::youMust()->chooseValue();
    }
  }

  public function handleValueChoice($value)
  {
    $card = self::drawAndMeld($value);
    if (count(array_unique(self::getBonuses())) >= 9) {
      self::win();
    } else {
      self::selfExecute($card);
    }
  }

}