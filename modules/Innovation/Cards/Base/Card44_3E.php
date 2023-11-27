<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Directions;
use Innovation\Enums\Icons;

class Card44_3E extends AbstractCard
{
  // Reformation (3rd edition):
  //   - You may tuck a card from your hand for every two [HEALTH] on your board.
  //   - You may splay your yellow or purple cards right.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return [
        'n'             => $this->game->intDivision(self::getStandardIconCount(Icons::HEALTH), 2),
        'location_from' => 'hand',
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