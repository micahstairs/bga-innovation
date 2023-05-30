<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

/* Writing - Age 1 */
class Card2 extends Card {

  public function initialExecution(ExecutionState $state) {
    // Non-demand: "Draw a 2"
    $this->game->executeDraw($state->getPlayerId(), 2);
  }

}
