<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;

class Card365 extends Card
{

  // Slide Rule
  //   - You may splay your yellow cards right.
  //   - Draw a card of value equal to the value of your lowest top card plus the number of colors you have splayed.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else {
      $value = $this->game->getMinAgeOnBoardTopCards(self::getPlayerId());
      for ($color = 0; $color < 5; $color++) {
        if (self::isSplayed($color)) {
          $value++;
        }
      }
      self::draw($value);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'        => true,
      'splay_direction' => $this->game::RIGHT,
      'color'           => [Colors::YELLOW],
    ];
  }

}