<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card219 extends AbstractCard
{

  // Safety Pin
  //   - ECHO: Draw and score a [7].
  //   - I DEMAND you return all cards of value higher than [6] from your hand! Draw a [6]!

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::drawAndScore(7);
    } else if (self::isDemand()) {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    return self::youMust()->return()->all()->minValue(7)->fromYourHand()->build();
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      self::draw(6);
    }
  }

}