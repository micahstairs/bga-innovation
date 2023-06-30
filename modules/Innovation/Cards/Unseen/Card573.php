<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card573 extends Card
{

  // Clown Car:
  //   - I demand you transfer a card from my score 
  //     pile to your board! If the transferred card 
  //     has no PROSPERITY, repeat this effect!

  public function initialExecution()
  {
      self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getLauncherId(),
      'location_from' => 'score',
      'location_to'   => 'board',
    ];
  }
  
  public function afterInteraction()
  {
      if (self::getNumChosen() > 0) {
        if (!$this->game->hasRessource(self::getLastSelectedCard(), $this->game::PROSPERITY)) {
          // "If the transferred card has no PROSPERITY, repeat this effect!"
          self::setNextStep(1);
        }
      }
  }  
   
}