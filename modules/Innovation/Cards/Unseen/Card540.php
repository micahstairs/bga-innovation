<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card540 extends Card
{

  // Swiss Bank Account:
  //   - Safeguard an available achievement of value equal to the 
  //     number of cards in your score pile. If you do, score all 
  //     cards in your hand of its value.
  //   - Draw a [6] for each secret in your safe.

  public function initialExecution(ExecutionState $state)
  {
    switch ($state->getEffectNumber()) {
      case 1:
        if ($this->game->countCardsInLocation($state->getPlayerId(), 'score') > 0) {
            $state->setMaxSteps(1);
        }
        break;
      case 2:
        for ($count = 0; $count < $this->game->countCardsInLocation($state->getPlayerId(), 'safe'); $count++) {
            $this->game->executeDraw($state->getPlayerId(), 6);
        }
        break;
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
      // "Safeguard an available achievement of value equal to the 
      //   number of cards in your score pile."
      return [
          'owner_from'    => 0,
          'location_from' => 'achievements',
          'location_to'   => 'safe',
          
          'age' => $this->game->countCardsInLocation($state->getPlayerId(), 'score'),
        ];

  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
      if ($state->getNumChosen() > 0) { // "If you do"
        // "score all cards in your hand of its value."
        $hand_cards = $this->game->getCardsInLocationKeyedByAge($state->getPlayerId(), 'hand');
        foreach ($hand_cards[$this->game->innovationGameState->get('age_last_selected')] as $card) {
            $this->game->scoreCard($card, $state->getPlayerId());
        }            
      }
  }

}