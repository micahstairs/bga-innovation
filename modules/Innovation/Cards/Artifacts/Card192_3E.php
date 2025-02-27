<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card192_3E extends AbstractCard
{
  // Time (3rd edition):
  //   - I COMPEL you to transfer a non-yellow top card with a [EFFICIENCY] from your board to my
  //     board! If you do, repeat this effect!

  public function getInteractionOptions(): array
  {
    return self::youMust()->non(Colors::YELLOW)->withIcon(Icons::EFFICIENCY)->fromYourBoard()->toMine()->build();
  }

  public function handleCardChoice(array $card)
  {
    self::setNextStep(1);
  }

  public function compelMightBeEffective(): bool
  {
    return count(self::filterByIcon(self::filterByColor(self::getTopCards(), Colors::NON_YELLOW), Icons::EFFICIENCY)) > 0;
  }

}