<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card2 extends AbstractCard {

  // Writing:
  //   - Draw a [2].

  public function initialExecution() {
    self::draw(2);
  }

}
