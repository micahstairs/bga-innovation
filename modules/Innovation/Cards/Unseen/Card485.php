<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card485 extends Card
{

  // Pilgrimage:
  //   - You may return any number of cards with consecutive values from your hand, starting
  //     with 1. If you do, safeguard an available achievement of value equal to the highest
  //     card returned.

  public function initialExecution(ExecutionState $state)
  {
    self::setMaxSteps(1);
    self::setAuxiliaryValue(1);
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if (self::getCurrentStep() == 1) {
      return [
        'can_pass'      => true,
        'location_from' => 'hand',
        'location_to'   => 'deck',
        'age'           => self::getAuxiliaryValue(),
      ];
    } else {
      return [
        'location_from' => 'achievements',
        'owner_from'    => 0,
        'location_to'   => 'safe',
        'age'           => self::getAuxiliaryValue() - 1,
      ];
    }
  }

  public function afterInteraction(Executionstate $state)
  {
    if (self::getCurrentStep() == 1) {
      if (self::getLastSelectedAge() == self::getAuxiliaryValue()) {
        self::setAuxiliaryValue(self::getAuxiliaryValue() + 1);
        self::setNextStep(1);
        self::setMaxSteps(2);
      }
    }
  }
}