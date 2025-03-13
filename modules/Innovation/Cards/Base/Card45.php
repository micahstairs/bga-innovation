<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card45 extends AbstractCard
{
  // Chemistry:
  //   - You may splay your blue cards right.
  //   - Draw and score a card of value one higher than the highest top card on your board and then
  //     return a card from your score pile.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else {
      self::drawAndScore(self::getMaxValue(self::getTopCards()) + 1);
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->splayRight()->withColor(Colors::BLUE)->build();
    } else {
      return self::youMust()->return()->fromYourScore()->build();
    }
  }

}