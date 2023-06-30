<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card567 extends Card
{

  // Iron Curtain:
  //   - Unsplay each splayed color on your board. For each color you 
  //     unsplay, return your top card of that color and safeguard an 
  //     available standard achievement.

  public function initialExecution()
  {
      $top_cards = $this->game->getTopCardsOnBoard(self::getPlayerId());
      $colors = array();
      foreach ($top_cards as $card) {
           $splay_dir = $this->game->getCurrentSplayDirection(self::getPlayerId(), $card['color']);
           if ($splay_dir > 0) {
                $colors[] = $card['color'];
                // "Unsplay each splayed color on your board."
                self::unsplay($card['color']);
           }
      }
      if (count($colors) > 0) {
        $this->game->setAuxiliaryArray($colors);
        self::setMaxSteps(2);
      }
  }

  public function getInteractionOptions(): array
  {
    
    if (self::getCurrentStep() == 1) {
        return [
          'can_pass'        => false,
          'n'               => 'all',
          'location_from'   => 'board',
          'location_to'     => 'deck',
          'color'           => $this->game->getAuxiliaryArray(),
        ];
    } else {
        // safeguard an available standard achievement.
        return [
            'can_pass'      => false,
            'n'             => 1, //count($this->game->getAuxiliaryArray()),
            'owner_from'    => 0,
            'location_from' => 'achievements',
            'location_to'   => 'safe',
          ];   
    }
  }
  
  public function afterInteraction()
  {

  }  

}