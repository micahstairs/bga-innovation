<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card454 extends AbstractCard
{
  // Greenland
  //   - I COMPEL you to return all your top cards with a [EFFICIENCY]!
  //   - Return all your top cards with a [PROSPERITY].

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isCompel()) {
      return [
        'n'                    => 'all',
        'location_from'        => Locations::BOARD,
        'return_keyword'       => true,
        'with_icon'            => Icons::EFFICIENCY,
        'refresh_selection'    => true,
        'enable_autoselection' => true,
      ];
    } else {
      return [
        'n'                    => 'all',
        'location_from'        => Locations::BOARD,
        'return_keyword'       => true,
        'with_icon'            => Icons::PROSPERITY,
        'refresh_selection'    => true,
        'enable_autoselection' => true,
      ];
    }
  }

}