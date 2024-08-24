<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card0 extends AbstractCard
{

  // Pottery:
  //   - You may return up to three cards from your hand. If you return any cards, draw and score a
  //     card of value equal to the number of cards you return.
  //   - Draw a [1].

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else {
      self::draw(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMay()->return()->minCards(1)->maxCards(3)->fromHand()->build();
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() > 0) {
      self::drawAndScore(self::getNumChosen());
    }
  }
}