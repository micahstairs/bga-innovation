<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card33 extends AbstractCard
{
  // Education:
  //   - You may return the highest card from your score pile. If you do, draw a card of value two
  //     higher than the highest card remaining in your score pile.

  public function getInteractionOptions(): array
  {
    return self::youMay()->return()->highest()->fromYourScore()->build();
  }

  public function handleCardChoice(array $card)
  {
    self::draw(self::getMaxValueInLocation(Locations::SCORE) + 2);
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::hasCards(Locations::SCORE);
  }

}