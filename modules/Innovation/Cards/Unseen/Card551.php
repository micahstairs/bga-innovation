<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card551 extends AbstractCard
{

  // Mafia
  //   - I demand you transfer your lowest secret to my safe!
  //   - Tuck a card from any score pile.
  //   - You may splay your red or yellow cards right.

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      $value = self::getMinValueInLocation('safe');
      return self::youMust()->value($value)->fromYourSafe()->toMine()->build();
    } else if (self::getEffectNumber() === 1) {
      return self::youMust()->tuck()->fromAnyScore()->build();
    } else {
      return self::youMay()->splayRight([Colors::RED, Colors::YELLOW])->build();
    }
  }

}