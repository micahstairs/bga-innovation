<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card566 extends Card
{

  // Fermi Paradox:
  //   - Reveal the top card of the [9] deck and the [10] deck. Return the top card of the [9] deck or the [10] deck.
  //   - If you have no cards on your board, you win. Otherwise, transfer all valued junked cards to your hand.

  public function initialExecution()
  {
      if (self::getEffectNumber() == 1) {
        // "Reveal the top card of the [9] deck and the [10] deck."
        $top_9 = $this->game->getDeckTopCard(9, $this->game::BASE);
        $top_10 = $this->game->getDeckTopCard(10, $this->game::BASE);
        
        if($top_9 !== null && $top_10 !== null) {
            self::setMaxSteps(2);
            self::reveal($top_9);
            self::reveal($top_10);
        } else if($top_9 !== null) {
            self::reveal($top_9);
            self::return($top_9);
        } else if($top_10 !== null) {
            self::reveal($top_10);
            self::return($top_10);
        }
        
      } else {
        // "If you have no cards on your board, you win."
        if ($this->game->countCardsInLocation(self::getPlayerId(), 'board') == 0) {
            $this->game->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('${You} have no cards on your board.'), array('You' => 'You'));
            $this->game->notifyAllPlayersBut(self::getPlayerId(), 'log', clienttranslate('${player_name} has no cards on their board.'), array('player_name' => $this->game->getColoredPlayerName(self::getPlayerId())));
            $this->game->innovationGameState->set('winner_by_dogma', self::getPlayerId()); // "You win"
            $this->game->trace('EOG bubbled from self::stPlayerInvolvedTurn Fermi Paradox');
            throw new \EndOfGame();
        } else {
            // "Otherwise, transfer all valued junked cards to your hand."
            $junked_cards = $this->game->getCardsInLocation(0, 'junk');
            foreach($junked_cards as $card) {
                if ($card['age'] !== null) {
                    $this->game->transferCardFromTo($card, self::getPlayerId(), 'hand');
                }
            }
        }
      }
  }

  public function getInteractionOptions(): array
  {
      if (self::getEffectNumber() == 1) {
        // "Return the top card of the [9] deck or the [10] deck."
        if (self::getCurrentStep() == 1) {
            return [
                'location_from' => 'revealed',
                'location_to'   => 'deck',
            ];     
        } else {
            // put the other card in your hand
            return [
                'location_from' => 'revealed',
                'location_to'   => 'hand',
              ];   
        }
      }
  }


  public function afterInteraction()
  {

  }  

}