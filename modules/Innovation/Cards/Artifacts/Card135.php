<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card135 extends AbstractCard
{

  // Dunhuang Star Chart
  // - 3rd edition:
  //   - Return all cards from your hand. Draw a card of value equal to the number of cards returned.
  // - 4th edition:
  //   - Return all cards from your hand. Draw a card of value equal to the number of cards you return.

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->return()->all()->fromYourHand();
  }

  public function afterInteraction()
  {
    self::draw(self::getNumChosen());
  }

}