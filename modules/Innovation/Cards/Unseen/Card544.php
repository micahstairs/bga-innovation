<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card544 extends Card
{

  // Triad:
  //   - If you have three or more cards in your hand, return a 
  //     card from your hand and splay the color of the returned 
  //     card right, tuck a card from your hand, and score a card from your hand.

  public function initialExecution(ExecutionState $state)
  {
    // "If you have three or more cards in your hand"
    if ($this->game->countCardsInHand($state->getPlayerId()) >= 3 ) {
        $state->setMaxSteps(3);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    switch ($state->getCurrentStep()) {
      case 1:
          // "return a card from your hand"
          return [
              'location_from' => 'hand',
              'location_to'   => 'deck',
            ];        
        break;
      case 2:
          // "tuck a card from your hand"
          return [
              'location_from' => 'hand',
              'location_to'   => 'board',

              'bottom_to'     => true,
            ];
        break;
      case 3:
          // "score a card from your hand."
          return [
              'location_from' => 'hand',
              'location_to'   => 'score',
            ];
        break;
    }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
      if ($state->getNumChosen() > 0) {
        if ($state->getCurrentStep() == 1) {
            // "splay the color of the returned card right"
            $this->game->splayRight($state->getPlayerId(), $state->getPlayerId(), 
              $this->game->innovationGameState->get('color_last_selected'));
        }            
      }
  }

}