<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card522 extends Card
{

  // Heirloom:
  //   - Transfer one of your secrets to the available achievements and draw a card of value one
  //     higher than the transferred card. If you don't, safeguard an available achievement of
  //     value equal to the value of your top red card.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return [
        'location_from' => 'safe',
        'owner_to'      => 0,
        'location_to'   => 'achievements',
      ];
    } else {
      return [
        'owner_from'    => 0,
        'location_from' => 'achievements',
        'location_to'   => 'safe',
        'age'           => self::getTopCardOfColor($this->game::RED)['faceup_age'],
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() == 1) {
      if (self::getNumChosen() > 0) {
        self::draw(self::getLastSelectedAge() + 1);
      } else {
        self::draw(1);
        if (self::getTopCardOfColor($this->game::RED)) {
          self::setMaxSteps(2);
        }
      }
    }
  }

}