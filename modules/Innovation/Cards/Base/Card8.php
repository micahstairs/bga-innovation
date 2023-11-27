<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card8 extends AbstractCard
{
  // The Wheel
  //   - Draw two [1].

  public function initialExecution()
  {
    self::draw(1);
    self::draw(1);
  }

}