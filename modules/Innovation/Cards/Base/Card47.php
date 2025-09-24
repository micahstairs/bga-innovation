<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card47 extends AbstractCard
{
  // Coal:
  // - 3rd edition:
  //   - Draw and tuck a [5].
  //   - You may splay your red cards right.
  //   - You may score any one of your top cards. If you do, also score the card beneath it.
  // - 4th edition:
  //   - Draw and tuck a [5].
  //   - You may splay your red cards right.
  //   - You may choose a color. If you do, score your top two cards of that color.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::drawAndTuck(5);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isSecondNonDemand()) {
      return self::youMay()->splayRight(Colors::RED)->build();
    } else {
      return self::youMay()->score()->fromYourBoard()->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::score(self::getTopCardOfColor(self::getColor($card)));
  }

}