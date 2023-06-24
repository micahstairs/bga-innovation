<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card546 extends Card
{

  // Private Eye:
  //   - I demand you reveal your hand! Transfer the card in your hand of my 
  //     choice to my board! Draw a [7]!
  //   - Score one of your secrets.
  //   - You may splay your blue cards right.

  public function initialExecution()
  {
    if (self::isDemand()) {
        foreach ($this->game->getCardsInLocation(self::getPlayerId(), 'hand') as $card) {
            $this->game->transferCardFromTo($card, self::getPlayerId(), 'revealed');
        }

        self::setMaxSteps(1);
    } else {
        switch (self::getEffectNumber()) {
          case 1:
            self::setMaxSteps(1);
            break;
          case 2:
            self::setMaxSteps(1);
            break;
        }
    }
  }

  public function getInteractionOptions(): array
  {
      if (self::isDemand()) {
        // "Transfer the card in your hand of my choice to my board!"
          return [
              'player_id'     => self::getLauncherId(),
              'owner_from'    => self::getPlayerId(),
              'location_from' => 'revealed',
              'owner_to'      => self::getLauncherId(),
              'location_to'   => 'board',
            ];         
      } else {
        
        switch (self::getEffectNumber()) {
          case 1:
            // "Score one of your secrets."
            return [
              'location_from' => 'safe',
              'location_to'   => 'score',
              'score_keyword' => true,
            ];
            break;
          case 2:
            // "You may splay your blue cards right."
            return [
              'can_pass'        => true,
              'splay_direction' => $this->game::RIGHT,
              'color'           => array(0),
            ];            
            break;
        }
      }
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
        self::draw(7);
        // put the cards back
        $this->game->gamestate->changeActivePlayer(self::getPlayerId());
        foreach ($this->game->getCardsInLocation(self::getPlayerId(), 'revealed') as $card) {
            //$this->game->transferCardFromTo($card, self::getPlayerId(), 'hand');
        }
    }
  }

}