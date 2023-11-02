<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card473 extends AbstractCard
{

  // Exoskeleton
  //   - I DEMAND you transfer all but the lowest cards in your hand to my score pile!
  //   - You may achieve a card from any player's hand, if eligible. If you do, and Exoskeleton was
  //     foreseen, repeat this effect.

  public function initialExecution()
  {
    if (self::isDemand()) {
      $lowestValue = self::getMinValueInLocation(Locations::HAND);
      foreach (self::getCards(Locations::HAND) as $card) {
        if (self::getValue($card) > $lowestValue) {
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
      'can_pass'            => true,
      'location_from'       => Locations::HAND,
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