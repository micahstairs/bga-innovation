<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;

class Card441 extends Card
{

  // Solar Sailing:
  //   - Draw and meld an [11]. If its color is not splayed aslant on your board, return all but
  ///    your top two cards of that color, and splay that color aslant. If there are four cards of
  //     that color on your board, you win.

  public function initialExecution()
  {
    $card = self::drawAndMeld(11);
    $color = $card['color'];
    $stack = self::getCardsKeyedByColor('board')[$color];
    if (self::getSplayDirection($color) !== $this->game::ASLANT) {
      $cardIds = [];
      for ($i = 0; $i < count($stack) - 2; $i++) {
        $cardIds[] = $stack[$i]['id'];
      }
      self::setAuxiliaryArray($cardIds);
      self::setAuxiliaryValue($color); // Track color to return
      self::setMaxSteps(1);
    } else if (count($stack) >= 4) {
      self::win();
    }
  }

  public function getInteractionOptions()
  {
    return [
      'location_from'                   => 'pile',
      'return_keyword'                  => true,
      'color'                           => [self::getAuxiliaryValue()],
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

  public function afterInteraction()
  {
    $color = self::getAuxiliaryValue();
    self::splayAslant($color);
    if (count(self::getCardsKeyedByColor('board')[$color]) >= 4) {
      self::win();
    }
  }

}