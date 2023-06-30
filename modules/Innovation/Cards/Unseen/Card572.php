<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card572 extends Card
{

  // Surveillance:
  //   - I demand you reveal your hand!
  //   - If the colors of cards in your hand matches the colors of revealed cards in an opponent's hand, and you have a card in your hand, you win.
  //   - Draw a [10].

  public function initialExecution()
  {
      if (self::isDemand()) {
          // "I demand you reveal your hand!"
          $cards_in_hand = $this->game->getCardsInLocation(self::getPlayerId(), 'hand');
          foreach ($cards_in_hand as $card) {
              $this->game->transferCardFromTo($card, self::getPlayerId(), 'revealed');
          }
      } else if (self::getEffectNumber() == 1) {
          
          // "If the colors of cards in your hand matches the colors of 
          //  revealed cards in an opponent's hand, and you have a card in your hand, you win."
          if ($this->game->countCardsInHand(self::getPlayerId()) > 0) {
              $my_hand = $this->game->getCardsInLocationKeyedByColor(self::getPlayerId(), 'hand');
          
              $opponent_ids = $this->game->getActiveOpponentIds(self::getPlayerId());
              
              foreach ($opponent_ids as $opponent_id) {
                  $eligible = true;
                  $card_revealed = $this->game->getCardsInLocationKeyedByColor($opponent_id, 'revealed');
                  if (count($card_revealed) > 0) {
                      for ($color = 0; $color < 5; $color++) {
                          if (($card_revealed[$color] > 0 && $my_hand[$color] == 0) || 
                            ($card_revealed[$color] == 0 && $my_hand[$color] > 0)) {
                            $eligible = false;
                          }
                      }
                      if ($eligible) {
                        //self::win();
                      } else {
                          
                          // put the cards back
                          $cards_in_revealed = $this->game->getCardsInLocation($opponent_id, 'revealed');
                          foreach ($cards_in_revealed as $card) {
                              $this->game->transferCardFromTo($card, $opponent_id, 'hand');
                          }
                      }
                  }
              }
          }
      } else {
          // "Draw a [10]."
          self::draw(10);
      }
  }

  public function getInteractionOptions(): array
  {
    return [
      'owner_from'    => self::getLauncherId(),
      'location_from' => 'score',
      'location_to'   => 'board',
    ];
  }
  
  public function afterInteraction()
  {
      if (self::getNumChosen() > 0) {
        if (!$this->game->hasRessource(self::getLastSelectedCard(), $this->game::PROSPERITY)) {
          // "If the transferred card has no PROSPERITY, repeat this effect!"
          self::setNextStep(1);
        }
      }
  }  
   
}