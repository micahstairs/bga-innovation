<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card44_4E extends AbstractCard
{
  // Reformation (4th edition):
  //   - You may tuck a card from your hand for every color on your board with [HEALTH].
  //   - You may splay your yellow or purple cards right.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'n'             => self::countColorsWithIcon(Icons::HEALTH),
        'location_from' => Locations::HAND,
        'tuck_keyword'  => true,
      ];
    } else {
      return [
        'can_pass'        => true,
        'splay_direction' => Directions::RIGHT,
        'color'           => [Colors::YELLOW, Colors::PURPLE],
      ];
    }
  }

}