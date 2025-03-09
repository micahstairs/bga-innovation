<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card36 extends AbstractCard
{
  // Printing Press:
  //   - You may return a card from your score pile. If you do, draw a card of value two higher
  //     than the top purple card on your board.
  //   - You may splay your blue cards right.

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMay()->return()->fromYourScore()->build();
    } else {
      return self::youMay()->splayRight()->withColor(Colors::BLUE)->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::draw(self::getValue(self::getTopCardOfColor(Colors::PURPLE)) + 2);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE) || self::canSplay();
  }

}