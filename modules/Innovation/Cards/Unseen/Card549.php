<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card549 extends Card
{

  // Black Market
  //   - You may safeguard a card from your hand. If you do, 
  //     reveal two available standard achievements. You may 
  //     meld a revealed card with no EFFICIENCY or AVATAR. 
  //     Return each revealed card you do not meld.'),

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
      
    if (self::getCurrentStep() == 1) {
      // "You may safeguard a card from your hand."
      return [
        'can_pass'      => true,
        'location_from' => 'hand',
        'location_to'   => 'safe',
      ];
    } else if (self::getCurrentStep() == 2) {
      // "reveal two available standard achievements"
      return [
        'n'             => 2,
        'owner_from'    => 0,
        'location_from' => 'achievements',
        'location_to'   => 'revealed',        
      ];
    } else if (self::getCurrentStep() == 3) {
      // "You may meld a revealed card with no EFFICIENCY or AVATAR."
      return [
        'can_pass'      => true,
        'location_from' => 'revealed',
        'location_to'   => 'board',
        
        'card_ids_are_in_auxiliary_array' => true,
        
        'meld_keyword'  => true,
      ];
    } else if (self::getCurrentStep() == 4) {  
      // "Return each revealed card you do not meld."
      return [
        'n'             => 'all',
        'location_from' => 'revealed',
        'location_to'   => 'deck',
      ];    }
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() == 1) {
        if (self::getNumChosen() > 0) {
            $card = $this->game->getCardInfo(self::getLastSelectedId());
            
            if ($card['location'] == 'safe' && $card['owner'] == self::getPlayerId()) { // "If you do,"
                self::setMaxSteps(4);
            }
        }
    } else if (self::getCurrentStep() == 2) {
        // Select meldable cards
        $card_id_array = array();
        $revealed_cards = $this->game->getCardsInLocation(self::getPlayerId(), 'revealed');
        foreach($revealed_cards as $card) {
            if (!$this->game->hasRessource($card, $this->game::EFFICIENCY) && 
                !$this->game->hasRessource($card, $this->game::AVATAR)) {
                $card_id_array[] = $card['id'];
            }
        }
        if (count($card_id_array) > 0) {
            $this->game->setAuxiliaryArray($card_id_array);
        } else {
            self::setNextStep(4); // skip the selection as none are eligible
        }
        
    }
  }
  
}