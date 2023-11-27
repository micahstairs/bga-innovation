<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Icons;

class Card128 extends AbstractCard
{

  // Babylonian Chronicles
  //   - I COMPEL you to transfer a top non-red card with [AUTHORITY] from your board to my board!
  //   - Draw and score a [3].

  public function initialExecution()
  {
    if (self::isCompel()) {
      self::setMaxSteps(1);
    } else {
      self::drawAndScore(3);
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getPlayerId(),
      'location_from' => 'board',
      'owner_to'      => self::getLauncherId(),
      'location_to'   => 'board',
      'with_icon'     => Icons::AUTHORITY,
      'color'         => Colors::NON_RED,
    ];
  }

}