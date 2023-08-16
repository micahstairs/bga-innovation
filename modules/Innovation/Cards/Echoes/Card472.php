<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card471 extends Card
{

  // Drone
  //   - You may achieve a card from any player's hand, if eligible. If you do, and Exoskeleton was
  //     foreseen, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from'       => 'hand',
      'owner_from'          => 'any player',
      'achieve_if_eligible' => true,
    ];
  }

  public function handleCardChoice(array $card)
  {
    if (self::wasForeseen()) {
      self::setNextStep(1);
    }
  }

}