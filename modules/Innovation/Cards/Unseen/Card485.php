<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card485 extends Card
{

  // Pilgrimage:
  //   - You may return a [1] from your hand. If you do, safeguard an available achievement of
  //     value equal to the returned card, then repeat this effect using the next higher value.

  public function initialExecution()
  {
    self::setMaxSteps(1);
    self::setAuxiliaryValue(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'can_pass'       => true,
        'location_from'  => 'hand',
        'return_keyword' => true,
        'age'            => self::getAuxiliaryValue(),
      ];
    } else {
      return [
        'safeguard_keyword' => true,
        'age'               => self::getAuxiliaryValue(),
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      if (self::getNumChosen() > 0 && self::getLastSelectedAge() === self::getAuxiliaryValue()) {
        self::setMaxSteps(2);
      }
    } else {
      self::setAuxiliaryValue(self::getAuxiliaryValue() + 1);
      self::setNextStep(1);
      self::setMaxSteps(1);
    }
  }
}