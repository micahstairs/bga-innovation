<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card481 extends Card
{

  // Palmistry:
  //   - Draw and meld a [1].
  //   - Return two cards from your hand. If you return two, draw and score a [2].

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::drawAndMeld(1);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'             => 2,
      'location_from' => 'hand',
      'location_to'   => 'deck',
    ];
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 2) {
      self::drawAndScore(2);
    }
  }
}