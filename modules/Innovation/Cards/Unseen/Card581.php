<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

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
    if (self::getEffectNumber() == 1) {
      return [
        'can_pass'        => true,
        'splay_direction' => $this->game::UP,
        'color'           => [$this->game::GREEN],
      ];
    } else {
      return [
        'owner_from'    => 'any other player',
        'location_from' => 'board',
        'meld_keyword'  => true,
        'color'         => self::getAllColorsOtherThan($this->game::YELLOW),
        'with_icon'     => $this->game::EFFICIENCY,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getEffectNumber() == 2) {
      if (self::getNumChosen() == 1) {
        self::selfExecute(self::getLastSelectedCard());
      } else {
        self::draw(11);
      }
    }
  }

}