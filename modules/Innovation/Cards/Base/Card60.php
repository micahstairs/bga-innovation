<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card60 extends AbstractCard
{
  // - Metric System
  //   - If your green cards are splayed right, you may splay any one color of your cards right.
  //   - You may splay your green cards right.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      if (self::getSplayDirection(Colors::GREEN) == Directions::RIGHT) {
        self::setMaxSteps(1);
      }
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayRight();
    } else {
      return self::youMay()->splayRight(Colors::GREEN);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return (self::getSplayDirection(Colors::GREEN) == Directions::RIGHT && self::canSplay(Colors::NON_GREEN)) || self::canSplay(Colors::GREEN);
  }

}