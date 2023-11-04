<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card422_3E extends AbstractCard
{

  // Wristwatch (3rd edition):
  //   - ECHO: Take a non-yellow top card from your board and tuck it.
  //   - For each visible bonus on your board, draw and tuck a card of that value, in ascending order.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::setMaxSteps(1);
    } else {
      $bonuses = self::getBonuses();
      sort($bonuses);
      foreach ($bonuses as $value) {
        self::drawAndTuck($value);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from' => Locations::BOARD,
      'tuck_keyword'  => true,
      'color'         => Colors::NON_YELLOW,
    ];
  }

}