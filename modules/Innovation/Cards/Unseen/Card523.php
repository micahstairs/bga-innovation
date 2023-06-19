<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card523 extends Card
{

  // Confession:
  //   - Return a top card with a AUTHORITY of each color from your board.
  //     If you return none, meld a card from your score pile, then draw and score a [4].
  //   - Draw a [4] for each [4] in your score pile.
  public function initialExecution(ExecutionState $state)
  {
    switch ($state->getEffectNumber()) {
      case 1:
        $card_id_array = array();
        foreach($this->game->getTopCardsOnBoard($state->getPlayerId()) as $card) {
            if ($this->game->hasRessource($card, 4)) {
                $card_id_array[] = $card['id'];
            }
        }
        if (count($card_id_array) > 0) {
            $this->game->setAuxiliaryArray($card_id_array);
            $state->setMaxSteps(1);
        } else {
            $this->game->setStep(1); // skip the first interaction returning top cards
            $state->setMaxSteps(2);
        }
        break;
      case 2:
        // Draw a [4] for each [4] in your score pile."
        $score_cards_by_age = $this->game->countCardsInLocationKeyedByAge($state->getPlayerId(), 'score');
        for ($count = 0; $count < $score_cards_by_age[4]; $count++) {
            $this->game->executeDraw($state->getPlayerId(), 4);
        }
        break;
    }
    
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->getCurrentStep() == 1) {
        // "Return a top card with a AUTHORITY of each color from your board."
        return [
          'n'                               => 'all',
          'location_from'                   => 'board',
          'location_to'                     => 'deck',
          'card_ids_are_in_auxiliary_array' => true,
        ];
    } else {
        // "meld a card from your score pile"
        return [
          'location_from' => 'score',
          'location_to' => 'board',
          'meld_keyword'  => true,
        ];
    }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }
  
  public function afterInteraction(Executionstate $state)
  {
      if ($state->getCurrentStep() == 1) {
        if ($state->getNumChosen() == 0) {
          $state->setMaxSteps(2);
        }
      } else {
        $this->game->executeDrawAndScore($state->getPlayerId(), 4);
      }
  }

}