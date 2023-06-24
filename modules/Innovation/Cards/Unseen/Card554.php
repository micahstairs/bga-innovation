<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;
use SebastianBergmann\Type\VoidType;

class Card554 extends Card
{

  // Slot Machine:
  //   - Draw and reveal a [1], [2], [3], [4], and [5], 
  //     then return them. If one drawn card is green, 
  //     splay your green or purple cards right. If two 
  //     drawn cards are green, also score all drawn cards. 
  //     If three drawn cards are green, you win.

  public function initialExecution()
  {
      for ($age = 1; $age < 6; $age++) {
        self::drawAndReveal($age);
      }
    
      $green_card_cnt = $this->game->countCardsInLocationKeyedByColor(self::getPlayerId(), 'revealed')[2];
      if ($green_card_cnt >= 1) {
        // "If one drawn card is green,"
        self::setMaxSteps(1);
      } else {
        $revealed_cards = $this->game->getCardsInLocation(self::getPlayerId(), 'revealed');
        // put the cards back
        foreach ($revealed_cards as $card) {
            $this->game->transferCardFromTo($card, 0, 'deck');
        }
      }
  }

  public function getInteractionOptions(): array
  {
    // "Splay your green or purple cards right."
    return [
      'splay_direction' => $this->game::RIGHT,
      'color'           => array($this->game::GREEN, $this->game::PURPLE),
    ];            
  }

  public function afterInteraction()
  {
      $revealed_cards = $this->game->getCardsInLocation(self::getPlayerId(), 'revealed');
      $green_card_cnt = $this->game->countCardsInLocationKeyedByColor(self::getPlayerId(), 'revealed')[2];
      if ($green_card_cnt >= 2) {
        // "If two drawn cards are green, also score all drawn cards."      
        foreach ($revealed_cards as $card) {
            self::score($card);
        }
        
        if ($green_card_cnt >= 3) {    
            // "If three drawn cards are green, you win."
            $this->game->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('${You} have drawn 3 green cards.'), array('You' => 'You'));
            $this->game->notifyAllPlayersBut(self::getPlayerId(), 'log', clienttranslate('${player_name} has 3 drawn green cards.'), array('player_name' => self::getColoredPlayerName(self::getPlayerId())));
            $this->innovationGameState->set('winner_by_dogma', self::getPlayerId()); // "You win"
            self::trace('EOG bubbled from self::stPlayerInvolvedTurn Slot Machine');
            throw new EndOfGame();      
        }
        
      } else {
        // put the cards back
        foreach ($revealed_cards as $card) {
            $this->game->transferCardFromTo($card, 0, 'deck');
        }
      }
  }

}