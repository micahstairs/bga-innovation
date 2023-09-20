<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card578 extends Card
{

  // Secret Santa:
  //   - I DEMAND you meld a card from my score pile!
  //   - Draw and score three [10].

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      self::drawAndScore(10);
      self::drawAndScore(10);
      self::drawAndScore(10);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getLauncherId(),
      'location_from' => Locations::SCORE,
      'owner_to'      => self::getPlayerId(),
      'meld_keyword'  => true,
    ];
  }

}