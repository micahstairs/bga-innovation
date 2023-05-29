<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

/* Pottery - Age 1 */
class Card0 extends Card {

  public function initialExecution(ExecutionState $state) {
    switch ($state->getEffectNumber()) {
      case 1:
        // Non-demand 1: "You may return up to three cards from your hand. If you return any cards, draw and score a card of value equal to the number of cards you return."
        $state->setMaxSteps(1);
        break;
      case 2:
        // Non-demand 2: "Draw a 1"
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
