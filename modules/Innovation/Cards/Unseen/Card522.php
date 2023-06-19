<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card522 extends Card
{

  // Heirloom:
  //   - Transfer one of your secrets to the available achievements and 
  //     draw a card of value one higher than the transferred card. If you don't, 
  //     safeguard an available achievement of value equal to the value of your top red card.

  public function initialExecution(ExecutionState $state)
  {
    $safe_cards = $this->game->getCardsInLocation($state->getPlayerId(), 'safe');
      
    if (count($safe_cards) == 1) {
        // "Transfer one of your secrets to the available achievements"
        $this->game->transferCardFromTo($safe_cards[0], 0, 'achievements');
        // "draw a card of value one higher than the transferred card."
        $this->game->executeDraw($state->getPlayerId(), ($safe_cards[0])['age'] + 1);
    } else if (count($safe_cards) > 1) {
      $state->setMaxSteps(1);
    } else {
        $top_red_card = $this->game->getTopCardOnBoard($state->getPlayerId(), 1);
        if ($top_red_card !== null) {
            $age = $top_red_card['age'];
        } else {
            $age = 1;
        }
        $this->game->executeDrawAndSafeguard($state->getPlayerId(), $age);
    }
    
  }

  public function getInteractionOptions(Executionstate $state): array
  {
      // "Transfer one of your secrets to the available achievements"
    return [
      'location_from' => 'safe',
      'owner_to'      => 0,
      'location_to'   => 'achievements',
    ];
  }

  public function afterInteraction(Executionstate $state)
  {
      if ($state->getNumChosen() > 0) {
        $this->game->executeDraw($state->getPlayerId(), $this->game->get('age_last_selected') + 1);
      } else {
        $top_red_card = self::getTopCardOnBoard($state->getPlayerId(), 1);
        if ($top_red_card !== null) {
            $age = $top_red_card['age'];
        } else {
            $age = 1;
        }
        $this->executeDrawAndSafeguard($state->getPlayerId(), $age);
      }
  }

}