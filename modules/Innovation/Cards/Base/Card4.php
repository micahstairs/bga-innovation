<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card4 extends Card
{

  // Metalworking:
  //   - Draw and reveal a [1]. If it has a [AUTHORITY], score it and repeat this effect.

  public function initialExecution(ExecutionState $state)
  {
    while (true) {
      $card = $this->game->executeDrawAndReveal($state->getPlayerId(), 1);
      if ($this->game->hasRessource($card, \Innovation::AUTHORITY)) {
        $this->notifications->notifyPresenceOfIcon(\Innovation::AUTHORITY);
        $this->game->scoreCard($card, $state->getPlayerId());
      } else {
        $this->notifications->notifyAbsenceOfIcon(\Innovation::AUTHORITY);
        $this->game->transferCardFromTo($card, $state->getPlayerId(), 'hand');
        return;
      }
    }
    
  }
}