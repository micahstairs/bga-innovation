<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card543 extends Card
{

  // Illuminati:
  //   - Reveal a card in your hand. Splay the card's color on 
  //     your board right. Safeguard the top card on your board 
  //     of that color. Safeguard an available achievement of 
  //     value one higher than the secret.

  public function initialExecution(ExecutionState $state)
  {
    if ($this->game->countCardsInHand($state->getPlayerId()) > 0) {
        $state->setMaxSteps(1);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    switch ($state->getCurrentStep()) {
      case 1:
          // "Reveal a card in your hand."
          return [
              'location_from' => 'hand',
              'location_to'   => 'revealed,hand',          
            ];
        break;
      case 2:
            // "Safeguard an available achievement of value one higher than the secret."
          return [
              'owner_from'    => 0,
              'location_from' => 'achievements',
              'location_to'   => 'safe',
              
              'age'           => $this->game->getAuxiliaryValue(),
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
            $color = $this->game->innovationGameState->get('color_last_selected');
            // "Splay the card's color on your board right."
            $this->game->splayRight($state->getPlayerId(), $state->getPlayerId(), $color);
            // "Safeguard the top card on your board of that color."
            $top_card = $this->game->getTopCardOnBoard($state->getPlayerId(), $color);
            if ($top_card !== null) {
                $this->game->safeguardCard($top_card, $state->getPlayerId());
                $state->setMaxSteps(2);
                $this->game->setAuxiliaryValue($top_card['age'] + 1);
            }
        }
      }
  }

}