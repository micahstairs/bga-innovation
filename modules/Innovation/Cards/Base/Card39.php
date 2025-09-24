<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\CardIds;
use Innovation\Enums\Directions;

class Card39 extends AbstractCard
{
  // Invention:
  // - 3rd edition:
  //   - You may splay right any one color of your cards currently splayed left. If you do, draw and score a [4].
  //   - If you have five colors splayed, each in any direction, claim the Wonder achievement.
  // - 4th edition:
  //   - You may choose a color you have splayed left and splay it right. If you do, draw and score a [4].
  //   - If you have five colors splayed, claim the Wonder achievement.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else if (self::isSecondNonDemand()) {
      if (self::countSplayedColors() == 5) {
        self::claim(CardIds::WONDER);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->splayRight(self::getSplayedColors(Directions::LEFT))->build();
  }

  public function handleSplayChoice(int $color, bool $splayChanged)
  {
    if ($splayChanged) {
      self::drawAndScore(4);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::countSplayedColors(Directions::LEFT) > 0) {
      return true;
    }
    return self::countSplayedColors() == 5 && self::isAvailable(CardIds::WONDER);
  }

}