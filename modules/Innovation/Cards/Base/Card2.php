<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;

class Card2 extends Card {

  // Writing:
  //   - Draw a [2].

  public function initialExecution() {
    self::draw(2);
  }

}
