<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card554 extends Card
{

  // Slot Machine:
  //   - Draw and reveal a [1], [2], [3], [4], and [5], then return them. If one drawn card is
  //     green, splay your green or purple cards right. If two drawn cards are green, also score
  //     all drawn cards. If three drawn cards are green, you win.

  public function initialExecution()
  {
    for ($i = 1; $i <= 5; $i++) {
      self::drawAndReveal($i);
    }
    if (self::countRevealedGreenCards() >= 1) {
      self::setMaxSteps(1);
    } else {
      foreach ($this->game->getCardsInLocation(self::getPlayerId(), 'revealed') as $card) {
        self::return($card);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'splay_direction' => $this->game::RIGHT,
      'color'           => array($this->game::GREEN, $this->game::PURPLE),
    ];
  }

  public function afterInteraction()
  {
    $numRevealedGreenCards = self::countRevealedGreenCards();
    if ($numRevealedGreenCards >= 2) {
      foreach ($this->game->getCardsInLocation(self::getPlayerId(), 'revealed') as $card) {
        self::score($card);
      }
      if ($numRevealedGreenCards >= 3) {
        $this->game->notifyPlayer(self::getPlayerId(), 'log', clienttranslate('${You} have drawn 3 green cards.'), array('You' => 'You'));
        $this->game->notifyAllPlayersBut(self::getPlayerId(), 'log', clienttranslate('${player_name} has 3 drawn green cards.'), array('player_name' => $this->game->getColoredPlayerName(self::getPlayerId())));
        $this->game->innovationGameState->set('winner_by_dogma', self::getPlayerId()); // "You win"
        $this->game->trace('EOG bubbled from self::stPlayerInvolvedTurn Slot Machine');
        throw new \EndOfGame();
      }
    } else {
      foreach ($this->game->getCardsInLocation(self::getPlayerId(), 'revealed') as $card) {
        self::return($card);
      }
    }
  }

  private function countRevealedGreenCards(): int
  {
    return $this->game->countCardsInLocationKeyedByColor(self::getPlayerId(), 'revealed')[$this->game::GREEN];
  }

}