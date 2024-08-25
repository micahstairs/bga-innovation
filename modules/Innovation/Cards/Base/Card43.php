<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

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
      return [
        'location_from' => Locations::BOARD,
        'owner_from'    => self::getPlayerId(),
        'owner_to'      => self::getLauncherId(),
        'color'         => Colors::NON_PURPLE,
        'with_icon'     => Icons::PROSPERITY,
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
    self::drawAndMeld(4);
  }

  public function demandMightBeEffective(): bool
  {
    $nonPurpleTopCards = self::filterByColor(self::getTopCards(), Colors::NON_PURPLE);
    return count(self::filterByIcon($nonPurpleTopCards, Icons::PROSPERITY)) > 0;
  }

}