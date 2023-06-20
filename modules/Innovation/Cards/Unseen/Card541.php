<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card541 extends Card
{

  // Attic:
  //   - You may score or safeguard a card from your hand.
  //   - Return a card from your score pile.
  //   - Draw and score a card of value equal to a card in your score pile.

  public function initialExecution(ExecutionState $state)
  {
    switch ($state->getEffectNumber()) {
      case 1:
        if ($this->game->countCardsInHand($state->getPlayerId()) > 0) {
            $state->setMaxSteps(1);
        }
        break;
      case 2:
        $state->setMaxSteps(1);
        break;
      case 3:
        $score_cards = $this->game->getCardsInLocation($state->getPlayerId(), 'score');
        if (count($score_cards) == 1) {
            $this->game->executeDrawAndScore($state->getPlayerId(), ($score_cards[0])['age']);
        } else if (count($score_cards) == 0) {
            $this->game->executeDrawAndScore($state->getPlayerId(), 1);
        } else {
            $state->setMaxSteps(1);
        }
        break;
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    switch ($state->getEffectNumber()) {
      case 1:
        switch ($state->getCurrentStep()) {
          case 1:
              return [
                'can_pass'         => true,
                'choose_yes_or_no' => true,
              ];
            break;
          case 2:
              // "You may score ... from your hand."
              return [
                  'location_from' => 'hand',
                  'location_to'   => 'score',                  
                ];
            break;
          case 3:
              // "You may ... safeguard a card from your hand."
              return [
                  'location_from' => 'hand',
                  'location_to'   => 'safe',
                ];
            break;
        }      
        break;
      case 2:
          // "Return a card from your score pile."
          return [
              'location_from' => 'score',
              'location_to'   => 'deck',
            ];
        break;
      case 3:
          // "Draw and score a card of value equal to a card in your score pile."
          $card_values_in_score_pile = array();
          $cards_by_age = $this->game->countCardsInLocationKeyedByAge($state->getPlayerId(), 'score');
          for($age = 1; $age < 12; $age++) {
              if ($cards_by_age[$age] > 0) {
                $card_values_in_score_pile[] = $age;
              }
          }
          return [
            'choose_value' => true,
            'age' => $card_values_in_score_pile,
          ];
        break;
    }      
    

  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }

  public function afterInteraction(Executionstate $state)
  {
    $choice = $this->game->getAuxiliaryValue();
    $player_id = $state->getPlayerId();
    if ($state->getEffectNumber() == 1) {
        if ($state->getCurrentStep() == 1) {
            if ($choice === 0) {
              $state->setMaxSteps(3); // TODO: skipping the scoring step doesn't work
              $state->setNextStep(3);
            }  else {
              $state->setMaxSteps(2);
            }
        }
    } else if ($state->getEffectNumber() == 3) {
        $this->game->executeDrawAndScore($player_id, $choice);
    }
  }

  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    if ($state->getEffectNumber() == 1)  {
        return [
          "message_for_player" => clienttranslate('${You} may make a choice'),
          "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
          "options"            => [
            [
              'value' => 1,
              'text'  => clienttranslate('Score a card from hand'),
            ],
            [
              'value' => 0,
              'text'  => clienttranslate('Safeguard a card from hand'),
            ],
          ],
        ];
    } else {
        return [
          "message_for_player" => clienttranslate('${You} must choose a value'),
          "message_for_others" => clienttranslate('${player_name} must choose a value'),
        ];
        
    }
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    $this->game->setAuxiliaryValue($choice);
  }
  
}