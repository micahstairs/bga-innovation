<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card74_3E extends AbstractCard
{
  // Railroad (4th edition):
  //   - Return all cards from your hand.
  //   - Draw three [6].
  //   - You may splay up any one color of your cards currently splayed right.

  public function initialExecution()
  {
    if (self::isSecondNonDemand()) {
      self::draw(6);
      self::draw(6);
      self::draw(6);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->return()->all()->fromYourHand()->build();
    } else {
      return self::youMay()->splayUp()->currentlySplayedRight()->build();
    }
  }

}