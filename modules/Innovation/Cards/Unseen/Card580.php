<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card580 extends AbstractCard
{

  // Denver Airport:
  //   - You may achieve one of your secrets regardless of eligibility.
  //   - You may splay your purple cards up.

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->achieve()->fromYourSafe()->build();
    } else {
      return self::youMay()->splayUp(Colors::PURPLE)->build();
    }
  }

}