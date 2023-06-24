<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card550 extends Card
{

  // Plot Voucher
  //   - Meld a card from your score pile. 
  //     Safeguard the lowest available standard achievement. 
  //     If you do, fully execute the melded card if it is your turn, otherwise self-execute it.

  public function initialExecution()
  {
      self::setAuxiliaryValue(-1);
      if ($this->game->getMinOrMaxAgeInLocation(0, 'achievements', 'MIN') > 0) {
        self::setMaxSteps(2);
      } else {
        self::setMaxSteps(1); // no achievements left
      }
  }

  public function getInteractionOptions(): array
  {
      
    if (self::getCurrentStep() == 1) {
      // "Meld a card from your score pile."
      return [
        'location_from' => 'score',
        'location_to'   => 'board',
        
        'meld_keyword' => true,
      ];
    } else {
      // "Safeguard the lowest available standard achievement. "
      return [
        'owner_from'    => 0,
        'location_from' => 'achievements',
        'location_to'   => 'safe',
        
        'age'           => $this->game->getMinOrMaxAgeInLocation(0, 'achievements', 'MIN'), // minimum
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() == 1) {
        if (self::getNumChosen() > 0) {
            self::setAuxiliaryValue(self::getLastSelectedId());
        }
    } else {
        if (self::getNumChosen() > 0) {
            $safe_card = $this->game->getCardInfo(self::getLastSelectedId());

            if ($safe_card['location'] == 'safe' && $safe_card['owner'] == self::getPlayerId()) { // "If you do,"
                if (self::getAuxiliaryValue() >= 0) { // can't execute a card you didn't meld
                    $card = $this->game->getCardInfo(self::getAuxiliaryValue());
                    if (self::getPlayerId() == self::getLauncherId()) {
                        // "fully execute the melded card if it is your turn,"
                        // TODO: $this->game->fullyExecute($card); 
                    } else {
                        // "otherwise self-execute it."
                        // TODO: self::selfExecute($card);
                    }
                }
            }
        }
    }
  }
  
}