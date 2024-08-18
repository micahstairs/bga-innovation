<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;
use Innovation\Enums\ValueSelectors;

class Card56_3E extends AbstractCard
{
  // Encyclopedia (3rd edition):
  //   - You may meld all the highest cards in your score pile. If you meld one of the highest, you
  //     must meld all of the highest.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'      => true,
      'meld_keyword'  => true,
      'n'             => 'all',
      'age'           => ValueSelectors::HIGHEST,
      'location_from' => Locations::SCORE,
    ];
  }

}