<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card24 extends AbstractCard
{
  // Philosophy:
  //   - You may splay left any one color of your cards.
  //   - You may score a card from your hand.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayLeft();
    } else {
      return self::youMay()->score()->fromYourHand();
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::canSplay() || self::hasCards(Locations::HAND);
  }

}