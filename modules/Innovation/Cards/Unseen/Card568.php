<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

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
      $topPurpleCard = self::getTopCardOfColor($this->game::PURPLE);
      if ($topPurpleCard !== null && $topPurpleCard['id'] == 84) {
        self::lose();
      }
    } else if (self::getEffectNumber() === 1) {
      self::score(self::getTopCardOfColor($this->game::PURPLE));
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'        => true,
      'splay_direction' => $this->game::UP,
      'color'           => [$this->game::RED, $this->game::BLUE],
    ];
  }
}