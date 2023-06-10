<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card0 extends Card {

  // Pottery:
  //   - You may return up to three cards from your hand. If you return any cards, draw and score a
  //     card of value equal to the number of cards you return.
  //   - Draw a [1].

  public function initialExecution(ExecutionState $state) {
    switch ($state->getEffectNumber()) {
      case 1:
        $state->setMaxSteps(1);
        break;
      case 2:
        $this->game->executeDraw($state->getPlayerId(), 1);
        break;
    }
  }

  public function getInteractionOptions(Executionstate $state): Array {
    return [
      'can_pass' => true,
      'n_min' => 1,
      'n_max' => 3,
      'location_from' => 'hand',
      'location_to' => 'deck',
    ];
  }

  public function afterInteraction(Executionstate $state) {
    if ($state->getNumChosen() > 0) {
      $this->game->executeDrawAndScore($state->getPlayerId(), $state->getNumChosen());
    }
  }
}
