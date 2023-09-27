<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card197_3E extends AbstractCard
{
  // United Nations Charter (3rd edition):
  //   - I COMPEL you to transfer all top cards on your board with a demand effect to my score pile!
  //   - If you have a top card on your board with a demand effect, draw a [10].

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(1);
    } else {
      foreach (self::getTopCards() as $card) {
        if ($card['has_demand'] === true) {
          self::draw(10);
          return;
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'                 => 'all',
      'owner_from'        => self::getPlayerId(),
      'location_from'     => Locations::BOARD,
      'owner_to'          => self::getLauncherId(),
      'location_to'       => Locations::SCORE,
      'has_demand_effect' => true,
    ];
  }

}