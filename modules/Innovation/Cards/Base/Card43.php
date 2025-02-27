<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card43 extends AbstractCard
{
  // Enterprise:
  // - 3rd edition:
  //   - I DEMAND you transfer a top non-purple card with a [PROSPERITY] from your board to my
  //     board! If you do, draw and meld a [4]!
  //   - You may splay your green cards right.
  // - 4th edition:
  //   - I DEMAND you transfer a top non-purple card with [PROSPERITY] from your board to my
  //     board! If you do, draw and meld a [4]!
  //   - You may splay your green cards right.

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return self::youMust()->non(Colors::PURPLE)->withIcon(Icons::PROSPERITY)->fromYourBoard()->toMine()->build();
    } else {
      return self::youMay()->splayRight()->withColor(Colors::GREEN)->build();
    }
  }

  public function handleCardChoice(array $card)
  {
    self::drawAndMeld(4);
  }

  public function demandMightBeEffective(): bool
  {
    $nonPurpleTopCards = self::filterByColor(self::getTopCards(), Colors::NON_PURPLE);
    return count(self::filterByIcon($nonPurpleTopCards, Icons::PROSPERITY)) > 0;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::canSplay(Colors::GREEN);
  }

}