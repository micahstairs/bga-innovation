<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card515 extends Card
{

    // Quackery:
    // Choose to either score a card from your hand, or draw a [4].
    // Reveal two cards in your hand. If you do, return both and 
    //     draw a card of value equal to the sum number of HEALTH and CONCEPT on the returned cards.

  public function initialExecution(ExecutionState $state)
  {
      
    switch ($state->getEffectNumber()) {
      case 1:
        $player_id = $state->getPlayerId();
        $hand_cnt = $this->game->countCardsInHand($player_id);
        if ($hand_cnt > 0) {
            $state->setMaxSteps(1);
        } else {
            // " or draw a [4]."
            $this->game->executeDraw($player_id, 4);
        }
        break;
      case 2:
        $state->setMaxSteps(1);
        break;
    }
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    switch ($state->getEffectNumber()) {
        case 1:
            switch ($state->getCurrentStep()) {
                case 1:
                    // "Choose to either score a card from your hand, or draw a [4]."
                    return [
                        'player_id' =>  $state->getPlayerId(), 
                        'choose_yes_or_no' => true
                    ];
                    break;
                case 2:
                    // "score a card from your hand"
                    return [
                        'player_id' =>  $state->getPlayerId(), 
                        'n' => 1,
                        'owner_from' =>  $state->getPlayerId(),
                        'location_from' => 'hand',
                        'owner_to' =>  $state->getPlayerId(),
                        'location_to'   => 'score',
                        'score_keyword' => true
                    ];                
                    break;
            }
            break;
        case 2:
            switch ($state->getCurrentStep()) {
                case 1:
                    // "Reveal two cards in your hand."
                    return [
                        'player_id' =>  $state->getPlayerId(), 
                        'n' => 2,
                        'owner_from' =>  $state->getPlayerId(),
                        'location_from' => 'hand',
                        'owner_to' =>  $state->getPlayerId(),
                        'location_to'   => 'revealed'
                    ];
                    break;
                case 2:
                    // "return both"
                    return [
                        'player_id' =>  $state->getPlayerId(), 
                        'n' => 2,
                        'owner_from' => $state->getPlayerId(),
                        'location_from' => 'revealed',
                        'owner_to' => 0,
                        'location_to'   => 'deck'
                    ];                
                    break;
            }
            break;
    }
  }

  public function afterInteraction(Executionstate $state) {
    switch ($state->getEffectNumber()) {
        case 2:
            switch ($state->getCurrentStep()) {
                case 1:
                    if ($state->getNumChosen() == 2) {
                        $player_id = $state->getPlayerId();
                        $revealed_cards = $this->game->getCardsInLocation($player_id, 'revealed');
                        $total = 0;
                        foreach ($revealed_cards as $card) {
                            $total += $this->game->countIconsOnCard($card, $this->game::HEALTH) + $this->game->countIconsOnCard($card, $this->game::CONCEPT);
                        }
                        $this->game->setAuxiliaryValue($total);                        
                    }
                    $this->game->incrementStepMax(1);
                    break;
                case 2:
                    if ($state->getNumChosen() == 2) {
                        $this->game->executeDraw($state->getPlayerId(), $this->game->getAuxiliaryValue());
                    }
                    break;            
            }
            break;
    }
  }
  
  public function getSpecialChoicePrompt(Executionstate $state): array
  {
    switch ($state->getEffectNumber()) {
        case 1:
            switch ($state->getCurrentStep()) {
                case 1:
                    $player_id = $state->getPlayerId();
                    $age_to_draw = $this->game->getAgeToDrawIn($player_id, 4);
                    $max_age = $this->game->getMaxAge();
                    return [
                      "message_for_player" => clienttranslate('${You} may make a choice'),
                      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
                      "options"            => [
                        [
                          'value' => 1,
                          'text'  => clienttranslate('Score a card from your hand')
                        ],
                        [
                          'value' => 0,
                          'text'  => $age_to_draw <= $max_age ? clienttranslate('Draw a ${age}') : clienttranslate('Finish the game (attempt to draw above ${age})'),
                          'age'   => $this->game->getAgeSquare($age_to_draw)
                        ],
                      ],
                    ];
                    break;
                }
        break;
    }
    
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    $player_id = $state->getPlayerId();
    if ($choice === 0) {
        // " or draw a [4]."
      $this->game->executeDraw($player_id, 4);
    } else {
      $state->setMaxSteps(2);
    }
  }

}