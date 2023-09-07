<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card568 extends Card
{

  // McCarthyism:
  //   - I demand you draw and meld an [8]! If Socialism is a top card on your board, you lose!
  //   - Score your top purple card.
  //   - You may splay your red or blue cards up.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::drawAndMeld(8);
      $topPurpleCard = self::getTopCardOfColor(Colors::PURPLE);
      if ($topPurpleCard !== null && $topPurpleCard['id'] == 84) {
        self::lose();
      }
    } else if (self::getEffectNumber() === 1) {
      self::score(self::getTopCardOfColor(Colors::PURPLE));
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'        => true,
      'splay_direction' => Directions::UP,
      'color'           => [Colors::RED, Colors::BLUE],
    ];
  }
}