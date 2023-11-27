<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card454 extends AbstractCard
{
  // Greenland
  //   - I COMPEL you to return one of your top cards with [EFFICIENCY]! If you do, repeat this effect.
  //   - Return one of your top cards with [PROSPERITY]. If you do, repeat this effect.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'location_from'        => Locations::BOARD,
      'return_keyword'       => true,
      'with_icon'            => self::isCompel() ? Icons::EFFICIENCY : Icons::PROSPERITY,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::setNextStep(1);
  }

}