<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card29 extends AbstractCard
{

  // Compass:
  // - 3rd edition:
  //   - I DEMAND you transfer a top non-green card with a [HEALTH] from your board to my board, and
  //     then you transfer a top card without a [HEALTH] from my board to your board!
  // - 4th edition:
  //   - I DEMAND you transfer a top non-green card with [HEALTH] from your board to my board, and
  //     then meld a top card without [HEALTH]!

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'location_from' => Locations::BOARD,
        'owner_from'    => self::getPlayerId(),
        'location_to'   => Locations::BOARD,
        'owner_to'      => self::getLauncherId(),
        'color'         => Colors::NON_GREEN,
        'with_icon'     => Icons::HEALTH,
      ];
    } else {
      return [
        'location_from' => Locations::BOARD,
        'owner_from'    => self::getLauncherId(),
        'location_to'   => Locations::BOARD,
        'owner_to'      => self::getPlayerId(),
        'color'         => Colors::NON_GREEN,
        'without_icon'  => Icons::HEALTH,
        'meld_keyword'  => self::isFourthEdition(),
      ];
    }
  }

}