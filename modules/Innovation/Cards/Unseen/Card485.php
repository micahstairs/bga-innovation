<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card485 extends Card
{

  // Pilgrimage:
  //   - You may return any number of cards with consecutive values from your hand, starting
  //     with 1. If you do, safeguard an available achievement of value equal to the highest
  //     card returned.

  public function initialExecution(ExecutionState $state)
  {
    $state->setMaxSteps(1);
    $this->game->setAuxiliaryValue(1);
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->getCurrentStep() == 1) {
      return [
        'can_pass'      => true,
        'location_from' => 'hand',
        'location_to'   => 'deck',
        'age'           => $this->game->getAuxiliaryValue(),
      ];
    } else {
      return [
        'location_from' => 'achievements',
        'owner_from'    => 0,
        'location_to'   => 'safe',
        'age'           => $this->game->getAuxiliaryValue() - 1,
      ];
    }
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->getCurrentStep() == 1) {
      if ($this->game->innovationGameState->get('age_last_selected') == $this->game->getAuxiliaryValue()) {
        $this->game->setAuxiliaryValue($this->game->getAuxiliaryValue() + 1);
        $state->setNextStep(1);
        $state->setMaxSteps(2);
      }
    }
  }
}