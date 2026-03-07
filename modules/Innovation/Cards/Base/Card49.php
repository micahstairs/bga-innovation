<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card49 extends AbstractCard
{
  // Banking:
  // - 3rd edition:
  //   - I DEMAND you transfer a top non-green card with a [INDUSTRY] from your board to my board.
  //     If you do, draw and score a [5]!
  //   - You may splay your green cards right.
  // - 4th edition:
  //   - I DEMAND you transfer a top non-green card with [INDUSTRY] from your board to my board.
  //     If you do, draw and score a [5]!
  //   - You may splay your green cards right.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->non(Colors::GREEN)->withIcon(Icons::INDUSTRY)->fromYourBoard()->toMine();
    } else {
      return self::youMay()->splayRight(Colors::GREEN);
    }
  }

  public function handleCardChoice(array $card)
  {
    self::drawAndScore(5);
  }

  public function demandMightBeEffective(): bool
  {
    $topNonGreenCards = self::filterByColor(self::getTopCards(), Colors::NON_GREEN);
    return count(self::filterByIcon($topNonGreenCards, Icons::INDUSTRY)) > 0;
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return self::canSplay(Colors::GREEN);
  }

}