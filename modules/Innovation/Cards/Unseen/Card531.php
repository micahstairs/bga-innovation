<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card531 extends Card
{

  // Chartreuse:
  //   - Draw and reveal a [3], a [4], a [5], and a [6]. Meld each drawn green card and each drawn
  //     yellow card, in any order. Return the other drawn cards.
  //   - You may splay your green or yellow cards right.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      for ($age = 3; $age <= 6; $age++) {
        self::drawAndReveal($age);
      }
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      if (self::getCurrentStep() === 1) {
        return [
          'n' => 'all',
          'location_from' => 'revealed',
          'meld_keyword'   => true,
          'color'         => [$this->game::GREEN, $this->game::YELLOW],
        ];
      } else {
        return [
          'n' => 'all',
          'location_from' => 'revealed',
          'location_to' => 'deck',
        ];
      }
    } else {
      return [
        'can_pass' => true,
        'splay_direction' => $this->game::RIGHT,
        'color' => [$this->game::GREEN, $this->game::YELLOW],
      ];
    }
  }

}