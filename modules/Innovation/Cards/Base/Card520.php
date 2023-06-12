<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Cards\ExecutionState;

class Card520 extends Card
{

  // El Dorado:
  //   - Draw and meld a [3], a [2], and a [1]. If all three have a PROSPERITY, score all cards in
  //     the [5] deck. If two or more have a PROSPERITY, splay your green and blue cards right.

  public function initialExecution(ExecutionState $state)
  {
    $numCardsWithProsperityIcons = 0;
    for ($i = 3; $i >= 1; $i--) {
      $card = $this->game->executeDrawAndMeld($state->getPlayerId(), $i);
      if ($this->game->hasRessource($card, \Innovation::PROSPERITY)) {
        $numCardsWithProsperityIcons++;
      }
    }
    if ($numCardsWithProsperityIcons == 3) {
      $cardsInDeck = $this->game->getCardsInLocationKeyedByAge(0, 'deck');
      foreach ($cardsInDeck[5] as $card) {
        if ($card['type'] == \Innovation::BASE) {
          $this->game->scoreCard($card, $state->getPlayerId());
        }
      }
    }
    if ($numCardsWithProsperityIcons >= 2) {
      $this->game->splayRight($state->getPlayerId(), $state->getPlayerId(), \Innovation::GREEN);
      $this->game->splayRight($state->getPlayerId(), $state->getPlayerId(), \Innovation::BLUE);
    }
  }
}