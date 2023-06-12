<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card520 extends Card {

  // El Dorado:
  //   - Draw and meld a 3, a 2, and a 1. If all three 
  //     have a PROSPERITY, score all cards in the 5 deck. If two or 
  //     more have a PROSPERITY, splay your green and blue cards right.

  public function initialExecution(ExecutionState $state) {
    switch ($state->getEffectNumber()) {
      case 1:
        $card_1 = $this->game->executeDrawAndMeld($state->getPlayerId(), 3);
        $card_2 = $this->game->executeDrawAndMeld($state->getPlayerId(), 2);
        $card_3 = $this->game->executeDrawAndMeld($state->getPlayerId(), 1);
        $cards_with_crowns_cnt = 0;
        if ($this->game->hasRessource($card_1, 1)) {
            $cards_with_crowns_cnt++;
        }            
        if ($this->game->hasRessource($card_2, 1)) {
            $cards_with_crowns_cnt++;
        }
        if ($this->game->hasRessource($card_3, 1)) {
            $cards_with_crowns_cnt++;
        }        
        if ($cards_with_crowns_cnt == 3) {
            $cards_in_decks = $this->game->getCardsInLocationKeyedByAge(0, 'deck');
            foreach($cards_in_decks[5] as $card) {
                if ($card['type'] == 0) { // don't score expansion cards
                    $this->game->scoreCard($card, $state->getPlayerId());
                }
            }
        }
        if ($cards_with_crowns_cnt >= 2) {
            $this->game->splayRight($state->getPlayerId(), $state->getPlayerId(), 2);
            $this->game->splayRight($state->getPlayerId(), $state->getPlayerId(), 0);
        }
        
        break;
    }
  }

}
