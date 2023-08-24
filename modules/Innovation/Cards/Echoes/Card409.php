<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card409 extends Card
{

  // Nylon
  // - 3rd edition
  //   - Draw and tuck an [8] for every three [INDUSTRY] on your board. If any of the tucked cards
  //     are green, repeat this effect.
  //   - You may splay your red cards up.
  // - 4th edition
  //   - Draw and tuck an [8] for every three [INDUSTRY] on your board. If any of the tucked cards
  //     were green, repeat this dogma effect.
  //   - You may splay your red cards up.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      do {
        $numCardsToTuck = $this->game->intDivision(self::getStandardIconCount($this->game::INDUSTRY), 3);

        $tuckedGreenCard = false;
        for ($i = 0; $i < $numCardsToTuck; $i++) {
          $card = self::drawAndTuck(8);
          if ($card['color'] == $this->game::GREEN) {
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
      'splay_direction' => $this->game::UP,
      'color'           => [$this->game::RED],
    ];
  }

}