<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card58 extends AbstractCard
{
  // Machine Tools:
  //   - Draw and score a card of value equal to the highest card in your score pile.

  public function initialExecution()
  {
    self::drawAndScore(self::getMaxValueInLocation(Locations::SCORE));
  }

}