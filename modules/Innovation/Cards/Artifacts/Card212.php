<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card212 extends AbstractCard
{
  // Where's Waldo
  //   - You win.

  public function initialExecution()
  {
    self::win();
  }

}