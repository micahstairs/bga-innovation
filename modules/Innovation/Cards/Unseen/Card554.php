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
    self::setMaxSteps(2);
    if (self::countRevealedGreenCards() == 0) {
      self::setNextStep(2);
    }
  }

  public function getInteractionOptions(): array
  {
      if (self::getCurrentStep() == 1) {
        return [
          'splay_direction' => $this->game::RIGHT,
          'color'           => array($this->game::GREEN, $this->game::PURPLE),
        ];
      } else {
        return [
          'n'                               => 'all',
          'location_from'                   => 'revealed',
          'location_to'                     => 'deck',
        ];
          
      }
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() == 1) {
        $numRevealedGreenCards = self::countRevealedGreenCards();
        if ($numRevealedGreenCards >= 2) {
          foreach (self::getCards( 'revealed') as $card) {
            self::score($card);
          }
          if ($numRevealedGreenCards >= 3) {
            self::notifyPlayer(clienttranslate('${You} have drawn 3 green cards.'), ['You' => 'You']);
            self::notifyOthers(clienttranslate('${player_name} has drawn 3 green cards.'), ['player_name' => $this->game->getColoredPlayerName(self::getPlayerId())]);
            self::win();
          }
        }
    }
  }

  private function countRevealedGreenCards(): int
  {
    return $this->game->countCardsInLocationKeyedByColor(self::getPlayerId(), 'revealed')[$this->game::GREEN];
  }

}