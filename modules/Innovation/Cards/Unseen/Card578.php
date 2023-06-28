<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card578 extends Card
{

  // Secret Santa:
  //   - I DEMAND you meld a card from my score pile!
  //   - Draw and score three [10].

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      self::drawAndScore(10);
      self::drawAndScore(10);
      self::drawAndScore(10);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getLauncherId(),
      'location_from' => 'score',
      'owner_to'      => self::getPlayerId(),
      'location_to'   => 'board',
    ];
  }

}