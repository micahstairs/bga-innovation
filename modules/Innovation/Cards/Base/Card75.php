<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card75 extends AbstractCard
{
  // Quantum Theory
  //   - You may return up to two cards from your hand. If you return two, draw a [10] and then
  //     draw and score a [10].

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMay()->return()->minCards(1)->maxCards(2)->fromYourHand();
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() == 2) {
      self::draw(10);
      self::drawAndScore(10);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}