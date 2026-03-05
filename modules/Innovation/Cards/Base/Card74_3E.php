<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card74_3E extends AbstractCard
{
  // Railroad (3rd edition):
  //   - Return all cards from your hand, then draw three [6].
  //   - You may splay up any one color of your cards currently splayed right.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->return()->all()->fromYourHand();
    } else {
      return self::youMay()->splayUp()->currentlySplayedRight();
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      self::draw(6);
      self::draw(6);
      self::draw(6);
    }
  }

}