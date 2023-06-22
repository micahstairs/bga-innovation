<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card3 extends Card {

  // Archery:
  //   - I DEMAND you draw a [1], then transfer the highest card in your hand to my hand!
  //   - [4th edition] Junk an available achievement of value [1] or [2].

  public function initialExecution(ExecutionState $state) {
    if (self::isDemand()) {
      self::draw(1);
    }
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(Executionstate $state): Array {
    if (self::isDemand()) {
      return [
        'location_from' => 'hand',
        'owner_to' => self::getLauncherId(),
        'location_to' => 'hand',
        'age' => $this->game->getMaxAgeInHand(self::getPlayerId()),
      ];
    } else {
      return [
        'owner_from' => 0,
        'location_from' => 'achievements',
        'location_to' => 'junk',
        'age_min' => 1,
        'age_max' => 2,
      ];
    }
  }
}
