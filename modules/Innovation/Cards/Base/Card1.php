<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card1 extends AbstractCard
{

  // Tools:
  //   - You may return three cards from your hand. If you do, draw and meld a [3].
  //   - You may return a [3] from your hand. If you do, draw three [1].

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->return()->exactly(3)->fromYourHand();
    } else {
      return self::youMay()->return()->value(3)->fromYourHand();
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand() && self::getNumChosen() === 3) {
      self::drawAndMeld(3);
    } else if (self::isSecondNonDemand() && self::getNumChosen() > 0) {
      self::draw(1);
      self::draw(1);
      self::draw(1);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }
}