<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Locations;

class Card44_4E extends AbstractCard
{
  // Reformation (4th edition):
  //   - You may splay your yellow or purple cards right.
  //   - You may tuck a card from your hand for every splayed color on your board.

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::YELLOW, Colors::PURPLE],
      ];
    } else {
      return [
        'can_pass'      => true,
        'n'             => self::countSplayedColors(),
        'location_from' => Locations::HAND,
        'tuck_keyword'  => true,
      ];
    }
  }

}