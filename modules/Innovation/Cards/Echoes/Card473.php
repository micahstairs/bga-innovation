<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card473 extends Card
{

  // Exoskeleton
  //   - I DEMAND you transfer all but the lowest cards in your hand to my score pile!
  //   - You may achieve a card from any player's hand, if eligible. If you do, and Exoskeleton was
  //     foreseen, repeat this effect.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $lowestValue = self::getMinValueInLocation('hand');
      foreach (self::getCards('hand') as $card) {
        if ($card['age'] > $lowestValue) {
          self::transferToScorePile($card, self::getLauncherId());
        }
      }
    } else {
      self::setMaxSteps(1);
    }
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