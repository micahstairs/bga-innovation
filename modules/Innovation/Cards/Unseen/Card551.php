<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card551 extends Card
{

  // Mafia
  //   - I demand you transfer your lowest secret to my safe!
  //   - Tuck a card from any score pile.
  //   - You may splay your red or yellow cards right.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'location_from' => 'safe',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'safe',
        'age'           => self::getMinValueInLocation('safe'),
      ];
    } else if (self::getEffectNumber() === 1) {
      return [
        'owner_from'    => 'any player',
        'location_from' => 'score',
        'tuck_keyword'  => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::RED, Colors::YELLOW],
      ];
    }
  }

}