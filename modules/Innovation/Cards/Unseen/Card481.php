<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card481 extends AbstractCard
{

  // Palmistry:
  //   - Draw and meld a [1].
  //   - Return two cards from your hand. If you do, draw and score a [2].

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::drawAndMeld(1);
    } else if (self::isSecondNonDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->return()->exactly(2)->fromYourHand();
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() === 2) {
      self::drawAndScore(2);
    }
  }
}