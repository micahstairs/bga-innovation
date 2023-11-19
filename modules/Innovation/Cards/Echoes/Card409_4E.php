<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card409_4E extends AbstractCard
{

  // Nylon (4th edition):
  //   - Draw and tuck three [8]. If any of the tucked cards are green, repeat this effect.
  //   - You may splay your red cards up.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      do {
        $tuckedGreenCard = false;
        for ($i = 0; $i < 3; $i++) {
          $card = self::drawAndTuck(8);
          if (self::isGreen($card)) {
            $tuckedGreenCard = true;
          }
        }
        if ($tuckedGreenCard) {
          self::notifyAll(clienttranslate("At least one of the tucked cards was green so the effect is repeating."));
        }
      } while ($tuckedGreenCard);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'        => true,
      'splay_direction' => Directions::UP,
      'color'           => [Colors::RED],
    ];
  }

}