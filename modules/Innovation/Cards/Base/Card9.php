<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card9 extends AbstractCard
{
  // Agriculture
  // - 3rd edition:
  //   - You may return a card from your hand. If you do, draw and score a card of value one higher than the card you returned.
  // - 4th edition:
  //   - You may return a card from your hand. If you do, draw and score a card of value one higher than the card you return.

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMay()->return()->fromYourHand();
  }

  public function handleCardChoice(array $card)
  {
    self::drawAndScore(self::getValue($card) + 1);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::HAND);
  }

}