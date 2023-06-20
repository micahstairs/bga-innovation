<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card542 extends Card
{

  // Sabotage:
  //   - I demand you draw a [6]! Reveal the cards in your hand! 
  //     Return the card of my choice from your hand! Tuck the 
  //     top card from your board and all cards from your score 
  //     pile of the same color as the returned card!

  public function initialExecution(ExecutionState $state)
  {
    // "I demand you draw a [6]!"
    $this->game->executeDraw($state->getPlayerId(), 6);
    foreach($this->game->getCardsInHand($state->getPlayerId()) as $card) {
        // "Reveal the cards in your hand!"
        $this->game->transferCardFromTo($card, $state->getPlayerId(), 'revealed');
        $state->setMaxSteps(2);
    }
    
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    switch ($state->getCurrentStep()) {
      case 1:
          // "Return the card of my choice from your hand!"
          return [
              'player_id'     =>  $state->getLauncherId(),
              'location_from' => 'revealed',
              'location_to'   => 'deck',
            ];        
        break;
      case 2:
          // "and all cards from your score pile of the same color as the returned card!"
          return [
              'n'             => 'all',
              'location_from' => 'score',
              'location_to'   => 'board',

              'bottom_to'     => true,
              
              'color'         => array($this->game->innovationGameState->get('color_last_selected')),
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
            // "Tuck the top card from your board"
            $top_card = $this->game->getTopCardOnBoard($state->getPlayerId(), 
                $this->game->innovationGameState->get('color_last_selected'));
            if ($top_card !== null) {
                $this->game->tuckCard($top_card, $state->getPlayerId());
            }
            // put the cards back
            $this->game->gamestate->changeActivePlayer($state->getPlayerId());
            foreach ($this->game->getCardsInLocation($state->getPlayerId(), 'revealed') as $card) {
                $this->game->transferCardFromTo($card, $state->getPlayerId(), 'hand');
            }
        }
      }
  }

}