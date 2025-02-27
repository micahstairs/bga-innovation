<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card35 extends AbstractCard
{
  // Experimentation:
  //   - Draw and meld a [5].

  public function initialExecution()
  {
    self::drawAndMeld(5);
  }

}