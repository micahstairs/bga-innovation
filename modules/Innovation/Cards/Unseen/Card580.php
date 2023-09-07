<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

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
    if (self::isFirstNonDemand()) {
      return [
        'location_from'   => 'safe',
        'achieve_keyword' => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::UP,
        'color'           => [Colors::PURPLE],
      ];
    }
  }

}