<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card551 extends AbstractCard
{

  // Mafia
  //   - I demand you transfer your lowest secret to my safe!
  //   - Tuck a card from any score pile.
  //   - You may splay your red or yellow cards right.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      $value = self::getMinValueInLocation('safe');
      return self::youMust()->value($value)->fromYourSafe()->toMine();
    } else if (self::getEffectNumber() === 1) {
      return self::youMust()->tuck()->fromAnyScore();
    } else {
      return self::youMay()->splayRight([Colors::RED, Colors::YELLOW]);
    }
  }

}