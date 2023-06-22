<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card1 extends Card
{

  // Tools:
  //   - You may return three cards from your hand. If you return three, draw and meld a [3].
  //   - You may return a [3] from your hand. If you do, draw three [1].

  public function initialExecution(ExecutionState $state)
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if (self::getEffectNumber() === 1) {
      return [
        'can_pass'      => true,
        'n'             => 3,
        'location_from' => 'hand',
        'location_to'   => 'deck',
      ];
    } else {
      return [
        'can_pass'      => true,
        'location_from' => 'hand',
        'location_to'   => 'deck',
        'age'           => 3,
      ];
    }
  }

  public function afterInteraction(Executionstate $state)
  {
    if (self::getEffectNumber() === 1) {
      if (self::getNumChosen() === 3) {
        self::drawAndMeld(3);
      }
    } else {
      if (self::getNumChosen() > 0) {
        for ($i = 0; $i < 3; $i++) {
          self::draw(1);
        }
      }
    }
  }
}