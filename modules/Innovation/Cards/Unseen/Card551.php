<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

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
        'age'           => self::getLowestSecretValue(),
      ];
    } else if (self::getEffectNumber() == 1) {
      return [
        'owner_from'    => 'any player',
        'location_from' => 'score',
        'location_to'   => 'board',
        'bottom_to'     => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::RIGHT,
        'color'           => [$this->game::RED, $this->game::YELLOW],
      ];
    }
  }

  private function getLowestSecretValue(): int
  {
    return $this->game->getMinOrMaxAgeInLocation(self::getPlayerId(), 'safe', 'MIN');
  }

}