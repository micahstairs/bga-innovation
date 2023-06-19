<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card521 extends Card
{

  // April Fool's Day:
  //   - Transfer the highest cards from your hand and score pile together to the 
  //     board of the player on your right. If you don't, claim the Folklore achievement.
  //   - Splay your yellow cards right, and unsplay your purple cards, or vice versa.
  public function initialExecution(ExecutionState $state)
  {
    switch ($state->getEffectNumber()) {
      case 1:
        $card_id_array = array();
        $max_age = $this->game->getMaxAgeInHand($state->getPlayerId());
        if ($max_age > 0) {
            $max_age_cards = $this->game->getCardsInLocationKeyedByAge($state->getPlayerId(), 'hand')[$max_age];
            foreach($max_age_cards as $card) {
                $card_id_array[] = $card['id'];
            }
        }
        $max_age = $this->game->getMaxAgeInScore($state->getPlayerId());
        if ($max_age > 0) {
            $max_age_cards = $this->game->getCardsInLocationKeyedByAge($state->getPlayerId(), 'score')[$max_age];
            
            foreach($max_age_cards as $card) {
                $card_id_array[] = $card['id'];
            }
        }
        if (count($card_id_array) == 0) {
            $this->game->claimSpecialAchievement($state->getPlayerId(), 598);
        } else {
            $state->setMaxSteps(1);
            $this->game->setAuxiliaryArray($card_id_array);
        }
        break;
      case 2:
        $state->setMaxSteps(1);
        break;
    }
    
  }

  public function getInteractionOptions(Executionstate $state): array
  {
    if ($state->getEffectNumber() == 1) {
        // "Transfer the highest cards from your hand and score pile together to the 
        //     board of the player on your right."
        return [
          'n'                               => 'all',
          'location_from'                   => 'hand,score',
          'owner_to'                        => $this->game->getActivePlayerIdOnRightOfActingPlayer(),
          'location_to'                     => 'board',
          'card_ids_are_in_auxiliary_array' => true,
        ];
    } else {
        // "Splay your yellow cards right, and unsplay your purple cards, or vice versa"
        return [
          'choose_yes_or_no'  => true,
        ];
    }
  }

  public function handleCardChoice(Executionstate $state, int $cardId)
  {
  }
  
  public function afterInteraction(Executionstate $state)
  {
  }

 public function getSpecialChoicePrompt(Executionstate $state): array
 {
    return [
      "message_for_player" => clienttranslate('${You} may make a choice'),
      "message_for_others" => clienttranslate('${player_name} may make a choice among the two possibilities offered by the card'),
      "options"            => [
        [
          'value' => 1,
          'text'  => clienttranslate('Splay yellow right and unsplay purple'),
        ],
        [
          'value' => 0,
          'text'  => clienttranslate('Splay purple right and unsplay yellow'),
        ],
      ],
    ];
  }

  public function handleSpecialChoice(Executionstate $state, int $choice): void
  {
    if ($choice === 1) {
      $this->game->splayRight($state->getPlayerId(), $state->getPlayerId(), 3);
      $this->game->unsplay($state->getPlayerId(), $state->getPlayerId(), 4);
    } else {
      $this->game->splayRight($state->getPlayerId(), $state->getPlayerId(), 4);
      $this->game->unsplay($state->getPlayerId(), $state->getPlayerId(), 3);      
    }
  }
  
}