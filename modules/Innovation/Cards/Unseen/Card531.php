<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card531 extends AbstractCard
{

  // Chartreuse:
  //   - Draw and reveal a [3], a [4], a [5], and a [6]. Meld each drawn green card and each drawn
  //     yellow card, in any order. Return the other drawn cards.
  //   - You may splay your green or yellow cards right.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      for ($age = 3; $age <= 6; $age++) {
        self::drawAndReveal($age);
      }
      self::setMaxSteps(2);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      if (self::isFirstInteraction()) {
        return self::youMust()->meld()->all()->withColor([Colors::GREEN, Colors::YELLOW])->fromYourRevealed()->build();
      } else {
        return self::youMust()->return()->all()->fromYourRevealed()->build();
      }
    } else {
      return self::youMay()->splayRight([Colors::GREEN, Colors::YELLOW])->build();
    }
  }

}