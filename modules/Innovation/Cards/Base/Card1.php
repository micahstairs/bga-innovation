<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

/* Tools - Age 1 */
class Card1 extends Card {

  public function initialExecution(ExecutionState $state) {
    // Non-demand 1: "You may return up to three cards from your hand. If you return any cards, draw and score a card of value equal to the number of cards you return."
    // Non-demand 2: "You may return a 3 from your hand. If you do, draw three 1."
    $state->setMaxSteps(1);
  }

  public function getInteractionOptions(Executionstate $state): Array {
    switch ($state->getEffectNumber()) {
      case 1:
        return [
          'can_pass' => true,
          'n' => 3,
          'location_from' => 'hand',
          'location_to' => 'deck',
        ];
      case 2:
        return [
          'can_pass' => true,
          'location_from' => 'hand',
          'location_to' => 'deck',
          'age' => 3,
        ];
    }
  }

  public function afterInteraction(Executionstate $state) {
    switch ($state->getEffectNumber()) {
      case 1:
        if ($state->getNumChosen() === 3) {
          $this->game->executeDrawAndMeld($state->getPlayerId(), 3);
        }
        break;
      case 2:
        if ($state->getNumChosen() > 0) {
          for ($i = 0; $i < 3; $i++) {
            $this->game->executeDraw($state->getPlayerId(), 1);
          }
        }
        break;
    }
  }
}
