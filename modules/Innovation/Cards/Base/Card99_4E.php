<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card99_4E extends AbstractCard
{
  // Databases (4th edition):
  //   - I DEMAND you return a number of cards from your score pile equal to the value of your
  //     highest achievement!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'              => self::getMaxValueInLocation(Locations::ACHIEVEMENTS),
      'location_from'  => Locations::SCORE,
      'return_keyword' => true,
    ];
  }

}