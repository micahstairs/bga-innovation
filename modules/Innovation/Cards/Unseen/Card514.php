<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card514 extends Card
{

  // Taqiyya:
  //   - Choose a color. Transfer all cards of that color on your board into your hand.
  //   - Draw and meld a [3]. If the melded card is a bottom card on your board, score it and any
  //     number of cards of its color in your hand.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(1);
    } else {
      $card = self::drawAndMeld(3);
      if (self::getBottomCardOfColor($card['color'])['id'] == $card['id']) {
        self::score($card);
        self::setAuxiliaryValue($card['color']);
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      return ['choose_color' => true];
    } else {
      return [
        'can_pass'      => true,
        'n_min'         => 1,
        'location_from' => 'hand',
        'score_keyword' => true,
        'color'         => [self::getAuxiliaryValue()],
      ];
    }
  }

  public function handleSpecialChoice(int $color)
  {
    foreach (self::getStack($color) as $card) {
      self::transferToHand($card);
    }
  }

}