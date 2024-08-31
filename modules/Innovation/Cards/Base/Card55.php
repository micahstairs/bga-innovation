<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card55 extends AbstractCard
{
  // Atomic Theory
  //   - You may splay your blue cards right.
  //   - Draw and meld a [7].

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      self::drawAndMeld(7);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->splayRight()->withColor(Colors::BLUE)->build();
  }

}