<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card580 extends Card
{

  // Denver Airport:
  //   - You may achieve one of your secrets regardless of eligibility.
  //   - You may splay your purple cards up.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      return [
        'location_from' => 'safe',
        'location_to'   => 'achievements',
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::UP,
        'color'           => [$this->game::PURPLE],
      ];
    }
  }

}