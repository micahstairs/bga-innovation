<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card518 extends Card
{

  // Spanish Inquisition:
  //   - I demand you return all but the highest cards from your hand and 
  //     all but the highest cards from your score pile!
  //   - If Spanish Inquisition is a top card on your board, return all 
  //     red cards from your board.

  public function initialExecution(ExecutionState $state)
  {
    if ($state->isDemand()) {
      $card_id_array = array();
      $max_age_in_hand = $this->game->getMaxAgeInHand($state->getPlayerId());
      foreach ($this->game->getCardsInLocation($state->getPlayerId(), 'hand') as $card) {
        if ($card['age'] != $max_age_in_hand) {
            $card_id_array[] = $card['id'];
        }
      }

      $max_age_in_score = $this->game->getMaxAgeInScore($state->getPlayerId());
      foreach ($this->game->getCardsInLocation($state->getPlayerId(), 'score') as $card) {
        if ($card['age'] != $max_age_in_score) {
            $card_id_array[] = $card['id'];
        }
      }
      
      if (count($card_id_array) > 0) {
        $state->setMaxSteps(2);
        $this->game->setAuxiliaryArray($card_id_array);
      }
    } else {
        // "If Spanish Inquisition is a top card on your board"
        $top_card = $this->game->getTopCardOnBoard($state->getPlayerId(), $this->game::RED); // top red card
        if ($top_card !== null && $top_card['id'] == 518) {
            $state->setMaxSteps(1);
        }
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->isDemand()) {
      // "I demand you return all but the highest cards from your hand and 
      //  all but the highest cards from your score pile!"
      return [
          'n' => 'all',
          'location_from' => 'hand,score',
          'location_to'   => 'deck',
          
          'card_ids_are_in_auxiliary_array' => true,
        ];
    } else {
      // "return all red cards from your board."
      return [
          'n' => 'all',
          'location_from' => 'pile',
          'location_to'   => 'deck',
          
          'color' => array($this->game::RED),
      ];
    }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
    if ($state->isDemand()) {
        if ($state->getNumChosen() > 0) { // "If you do"
            // "transfer the top card of that color from your board to mine!"
            $top_card_of_color = $this->game->getTopCardOnBoard($state->getPlayerId(), $this->game->getAuxiliaryValue());
            if ($top_card_of_color !== null) {
                $this->game->transferCardFromTo($top_card_of_color, $state->getLauncherId(), 'board');
            }
        }
    }
  }

  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    return [
      "message_for_player" => clienttranslate('${You} must choose a color'),
      "message_for_others" => clienttranslate('${player_name} must choose a color'),
    ];
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    $this->game->setAuxiliaryValue($choice);
  }

}