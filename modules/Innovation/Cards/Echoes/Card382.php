<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card382 extends AbstractCard
{

  // Stove
  //   - ECHO: Score a top card from your board without [INDUSTRY].
  //   - Draw and tuck a [4]. If your top card of the tucked card's color has value less than 4,
  //     draw and score a [4].
  //   - You may splay your green cards right.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else if (self::isFirstNonDemand()) {
      $tuckedCard = self::drawAndTuck(4);
      $topCard = self::getTopCardOfColor(self::getColor($tuckedCard));
      if (self::getValue($topCard) < 4) {
        self::drawAndScore(4);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return self::youMust()->score()->withoutIcon(Icons::INDUSTRY)->fromYourBoard()->build();
    } else {
      return self::youMay()->splayRight(Colors::GREEN)->build();
    }
  }

}