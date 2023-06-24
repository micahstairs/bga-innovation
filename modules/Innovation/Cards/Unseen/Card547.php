<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card547 extends Card
{

  // Camouflage:
  //   - Choose to either safeguard exactly two top cards of different
  //     color and equal value on your board, or score exactly two of 
  //     your secrets of equal value.
  //   - Draw a [7] for each special achievement you have.

  public function initialExecution(ExecutionState $state)
  {
    switch ($state->getEffectNumber()) {
      case 1:
        
        $state->setMaxSteps(1);
        break;
      case 2:
        $achievements = $this->game->getCardsInLocation($state->getPlayerId(), 'achievements');
        foreach($achievements as $card) {
            // "Draw a [7] for each special achievement you have."
            if ($card['age'] == null) {
                $this->game->executeDraw($state->getPlayerId(), 7);
            }
        }
        break;
    }
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