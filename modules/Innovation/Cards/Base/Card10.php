<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card10 extends AbstractCard
{
  // Domestication
  //   - Meld the lowest card in your hand. Draw a [1].

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->meld()->lowest()->fromYourHand();
  }

  public function afterInteraction()
  {
    self::draw(1);
  }

}