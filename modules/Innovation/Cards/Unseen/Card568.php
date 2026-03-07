<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card568 extends AbstractCard
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
      if ($topPurpleCard !== null && self::getId($topPurpleCard) == 84) {
        self::lose();
      }
    } else if (self::getEffectNumber() === 1) {
      self::score(self::getTopCardOfColor(Colors::PURPLE));
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMay()->splayUp([Colors::RED, Colors::BLUE]);
  }
}