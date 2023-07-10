<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card522 extends Card
{

  // Heirloom:
  //   - Transfer one of your secrets to the available achievements and 
  //     draw a card of value one higher than the transferred card. If you don't, 
  //     safeguard an available achievement of value equal to the value of your top red card.

  public function initialExecution()
  {
    $secrets = $this->game->getCardsInLocation(self::getPlayerId(), 'safe');
    if (count($secrets) == 1) {
      $this->game->transferCardFromTo($secrets[0], 0, 'achievements');
      self::draw($secrets[0]['age'] + 1);
    } else if (count($secrets) > 1) {
      self::setMaxSteps(1);
    } else {
        $topRedCard = self::getTopCardOfColor($this->game::RED);
        if ($topRedCard !== null) {
          self::setNextStep(2);
          self::setMaxSteps(2);
        }
    }

  }

  public function getInteractionOptions(): array
  {
      if (self::getCurrentStep() == 1) {
        // "Transfer one of your secrets to the available achievements"
        return [
          'location_from' => 'safe',
          'owner_to'      => 0,
          'location_to'   => 'achievements',
        ];
      } else {
        // "safeguard an available achievement of value equal to the value of your top red card."
        $topRedCard = self::getTopCardOfColor($this->game::RED);
        return [
          'owner_from'    => 0,
          'location_from' => 'achievements',
          'location_to'   => 'safe',
          'age'           => $topRedCard['faceup_age'],
        ];
      }    
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() == 1) {
        if (self::getNumChosen() > 0) {
          self::draw(self::getLastSelectedAge() + 1);
        } else { // "If you don't, "
            $topRedCard = self::getTopCardOfColor($this->game::RED);
            if ($topRedCard === null) {
                self::setMaxSteps(2);
            }
          
        }
    }
  }

}