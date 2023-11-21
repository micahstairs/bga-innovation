<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card99_3E extends AbstractCard
{
  // Databases (3rd edition):
  //   - I DEMAND you return half (rounded up) of the cards in your score pile!

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'n'              => ceil(self::countCards(Locations::SCORE) / 2),
      'location_from'  => Locations::SCORE,
      'return_keyword' => true,
    ];
  }

}