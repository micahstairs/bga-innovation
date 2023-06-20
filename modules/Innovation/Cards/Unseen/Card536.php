<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card536 extends Card
{

  // Reconnaissance:
  //   - I_demand you reveal your hand!
  //   - Draw and reveal three [7]. Return two of the drawn cards. 
  //     You may splay the color of the card not returned right.

  public function initialExecution(ExecutionState $state)
  {
    if ($state->isDemand()) {
        $this->game->revealHand($state->getPlayerId());
    } else {
        // "Draw and reveal three [7]."
        $card_id_array = array();
        for ($ctr = 0; $ctr < 3; $ctr++) {
            $card = $this->game->executeDrawAndReveal($state->getPlayerId(), 7);
            $card_id_array[] = $card['id'];
        }
        $this->game->setAuxiliaryArray($card_id_array);
        $state->setMaxSteps(2);
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
      if ($state->isNonDemand()) {
        if ($state->getCurrentStep() == 1) {
            // "Return two of the drawn cards."
            return [
              'n'             => 2,
              'location_from' => 'revealed',
              'location_to'   => 'deck',
              'card_ids_are_in_auxiliary_array' => true,
            ];
        } else {
          // "You may splay the color of the card not returned right."
          return [
            'can_pass'        => true,
            'splay_direction' => $this->game::RIGHT,
            'color'           => array($this->game->getAuxiliaryValue()),
          ];
        }
      }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->isNonDemand()) {
        if ($state->getCurrentStep() == 1) {
            $revealed_card = $this->game->getCardsInLocation($state->getPlayerId(), 'revealed')[0];
            $this->game->transferCardFromTo($revealed_card, $state->getPlayerId(), 'hand');
            $this->game->setAuxiliaryValue($revealed_card['color']);
        }
    }
  }

}