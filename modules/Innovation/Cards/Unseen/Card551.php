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
      // "I demand you transfer your lowest secret to my safe!"
      return [
        'location_from' => 'safe',
        'owner_to'      => self::getLauncherId(),
        'location_to'   => 'safe',
        
        'age' => $this->game->getMinOrMaxAgeInLocation(self::getPlayerId(), 'safe', 'MIN'),
      ];
    } else if (self::getEffectNumber() == 1) {
      // "Tuck a card from any score pile."
      return [
        'owner_from'    => 'any player',
        'location_from' => 'score',
        'location_to'   => 'board',
        'bottom_to'     => true,
      ];
    } else {
      // "You may splay your red or yellow cards right."
        return [
          'can_pass'        => true,
          'splay_direction' => $this->game::RIGHT,
          'color'           => array(1,3),
        ];            
    }
  }
  
  public function afterInteraction()
  {
  }
  
}