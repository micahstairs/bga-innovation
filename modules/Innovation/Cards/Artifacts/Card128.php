<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;

class Card128 extends Card
{

  // Babylonian Chronicles
  //   - I COMPEL you to transfer a top non-red card with a [AUTHORITY] from your board to my board!
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
      'with_icon'     => $this->game::AUTHORITY,
      'color'         => self::getAllColorsOtherThan($this->game::RED),
    ];
  }

}