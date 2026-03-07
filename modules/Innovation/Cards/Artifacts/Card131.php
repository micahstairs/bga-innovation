<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card131 extends AbstractCard
{

  // Holy Grail
  //   - Return a card from your hand. Claim an available achievement of matching value ignoring eligibility.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->return()->fromYourHand();
    } else {
      return self::youMust()->achieve()->value(self::getLastSelectedAge());
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setMaxSteps(2);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}