<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card508 extends Card
{

  // Red Envelope:
  //   - Choose a value of which you have exactly two or three 
  //     cards altogether in your hand and score pile. Transfer 
  //     those cards to the score pile of the player on your right.
  //   - You may score exactly two or three cards from your hand.

  public function initialExecution()
  {
      if (self::getEffectNumber() === 1) {
        $hand_cards = $this->game->countCardsInLocationKeyedByAge(self::getPlayerId(), 'hand');
        $score_cards = $this->game->countCardsInLocationKeyedByAge(self::getPlayerId(), 'score');
        $age_array = array();
        for ($age = 1; $age < 12; $age++) {
            if ($hand_cards[$age] + $score_cards[$age] == 2 || 
            $hand_cards[$age] + $score_cards[$age] == 3) {
                $age_array[] = $age;
            }
        }
        if (count($age_array) > 0) {
            self::setMaxSteps(1);
            $this->game->setAuxiliaryValueFromArray($age_array);
        }
      } else {
        if ($this->game->countCardsInHand(self::getPlayerId()) >= 2) {
            self::setMaxSteps(1);
        }
      }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {  
      return [
        'choose_value' => true,
        'age' => $this->game->getAuxiliaryValueAsArray(),
      ];
    } else {
      return [
        'n_min' => 2,
        'n_max' => 3,
        'can_pass' => true,
        
        'location_from' => 'hand',
        'location_to' => 'score',
        
        'score_keyword' => true,
      ];
    }
  }

  public function handleSpecialChoice(int $value) {
    self::setAuxiliaryValue($value);
  }

  public function afterInteraction() {
    if (self::getEffectNumber() === 1) {  
      // "Transfer those cards to the score pile of the player on your right."
      $hand_cards = $this->game->getCardsInLocationKeyedByAge(self::getPlayerId(), 'hand');
      $score_cards = $this->game->getCardsInLocationKeyedByAge(self::getPlayerId(), 'score');
      $dest_player_id = $this->game->getActivePlayerIdOnRightOfActingPlayer();
      foreach ($hand_cards[self::getAuxiliaryValue()] as $card) {
        $this->game->transferCardFromTo($card, $dest_player_id, 'score');
      }
      foreach ($score_cards[self::getAuxiliaryValue()] as $card) {
        $this->game->transferCardFromTo($card, $dest_player_id, 'score');
      }
    }
  }

}