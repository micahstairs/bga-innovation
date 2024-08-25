<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card24 extends AbstractCard
{
  // Philosophy:
  //   - You may splay left any one color of your cards.
  //   - You may score a card from your hand.

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayLeft()->build();
    } else {
      return self::youMay()->score()->fromYourHand()->build();
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::canSplayLeft() || self::hasCards(Locations::HAND);
  }

}