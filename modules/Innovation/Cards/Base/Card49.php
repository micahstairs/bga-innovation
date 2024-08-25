<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

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

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'location'   => Locations::BOARD,
        'owner_from' => self::getPlayerId(),
        'owner_to'   => self::getLauncherId(),
        'color'      => Colors::NON_GREEN,
        'with_icon'  => Icons::INDUSTRY,
        'age'        => 5,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::GREEN],
      ];
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

}