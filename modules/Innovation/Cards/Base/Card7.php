<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card7 extends AbstractCard
{
  // Sailing
  //   - Draw and meld a [1].

  public function initialExecution()
  {
    self::drawAndMeld(1);
  }

}