<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;

class Card580 extends AbstractCard
{

  // Denver Airport:
  //   - You may achieve one of your secrets regardless of eligibility.
  //   - You may splay your purple cards up.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->achieve()->fromYourSafe();
    } else {
      return self::youMay()->splayUp(Colors::PURPLE);
    }
  }

}