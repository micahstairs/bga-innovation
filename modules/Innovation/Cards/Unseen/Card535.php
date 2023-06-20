<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card535 extends Card
{

  // Placebo:
  //   - Return one or more top cards of one color from your board, 
  //     from the top. Draw a [7] for each card you return. If 
  //     you return exactly one [7], draw an [8].

  public function initialExecution(ExecutionState $state)
  {
    $this->game->setAuxiliaryValue2(0);
    $this->game->setAuxiliaryValue(0);
    $state->setMaxSteps(2);
  }

  public function getInteractionOptions(Executionstate $state): array
  {
      if ($state->getCurrentStep() == 1) {
        // "Return one"
        return [
          'can_pass' => false,
          'location_from' => 'board',
          'location_to'   => 'deck',
        ];          
      } else {
         // "or more top cards of one color from your board"
        return [
          'can_pass' => true,
          'location_from' => 'board',
          'location_to'   => 'deck',
          
          'color' => array($this->game->innovationGameState->get('color_last_selected')),
        ];
      }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->getCurrentStep() == 1) {
        if ($state->getNumChosen() > 0) {
            $state->setMaxSteps(2); // allow continue returning
            $this->game->setAuxiliaryValue2($this->game->getAuxiliaryValue2() + 1);
            if ($this->game->innovationGameState->get('age_last_selected') == 7) {
                $this->game->setAuxiliaryValue($this->game->getAuxiliaryValue() + 1);
            }
        }
    } else {
        if ($state->getNumChosen() > 0) {
            $state->setNextStep(1);
            $this->game->setAuxiliaryValue2($this->game->getAuxiliaryValue2() + 1);
            if ($this->game->innovationGameState->get('age_last_selected') == 7) {
                $this->game->setAuxiliaryValue($this->game->getAuxiliaryValue() + 1);
            }
        } else {
            // "Draw a 7 for each card you return.
            for ($count = 0; $count < $this->game->getAuxiliaryValue2(); $count++) {
                $this->game->executeDraw($state->getPlayerId(), 7);                
            }
            
            // "If you return exactly one 7, draw an 8."
            if ($this->game->getAuxiliaryValue() == 1) {
                $this->game->executeDraw($state->getPlayerId(), 8);
            }
        }
    }
  }

}