<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card581 extends Card
{

  // Ride-Hailing:
  //   - You may splay your green cards up.
  //   - Meld a top non-yellow card with a [EFFICIENCY] from another player's board. If you do, self-execute it. Otherwise, draw an [11].

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::UP,
        'color'           => [Colors::GREEN],
      ];
    } else {
      return [
        'owner_from'    => 'any other player',
        'location_from' => 'board',
        'meld_keyword'  => true,
        'color'         => Colors::NON_YELLOW,
        'with_icon'     => Icons::EFFICIENCY,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondNonDemand()) {
      if (self::getNumChosen() === 1) {
        self::selfExecute(self::getLastSelectedCard());
      } else {
        self::draw(11);
      }
    }
  }

}