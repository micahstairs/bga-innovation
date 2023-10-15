<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card192_3E extends AbstractCard
{
  // Time (3rd edition):
  //   - I COMPEL you to transfer a non-yellow top card with a [EFFICIENCY] from your board to my
  //     board! If you do, repeat this effect!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getPlayerId(),
      'location_from' => Locations::BOARD,
      'owner_to'      => self::getLauncherId(),
      'location_to'   => Locations::BOARD,
      'with_icon'     => Icons::EFFICIENCY,
      'color'         => Colors::NON_YELLOW,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::setNextStep(1);
  }

}