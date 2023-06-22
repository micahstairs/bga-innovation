<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card2 extends Card {

  // Writing:
  //   - Draw a [2].

  public function initialExecution(ExecutionState $state) {
    self::draw(2);
  }

}
