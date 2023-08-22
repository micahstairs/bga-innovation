<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card554 extends Card
{

  // Slot Machine:
  //   - Draw and reveal a [1], [2], [3], [4], and [5]. If one drawn card is green, splay your
  //     green or purple cards right. If two drawn cards are green, score all drawn cards,
  //     otherwise return them. If three drawn cards are green, you win.

  public function initialExecution()
  {
    for ($i = 1; $i <= 5; $i++) {
      self::drawAndReveal($i);
    }
    self::setMaxSteps(2);
    if (self::countRevealedGreenCards() === 0) {
      self::setNextStep(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return [
        'splay_direction' => $this->game::RIGHT,
        'color'           => [$this->game::GREEN, $this->game::PURPLE],
      ];
    } else {
      return [
        'n'              => 'all',
        'location_from'  => 'revealed',
        'return_keyword' => true,
      ];

    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction()) {
      $numRevealedGreenCards = self::countRevealedGreenCards();
      if ($numRevealedGreenCards >= 2) {
        foreach (self::getCards('revealed') as $card) {
          self::score($card);
        }
        if ($numRevealedGreenCards >= 3) {
          self::notifyPlayer(clienttranslate('${You} revealed three green cards.'), ['You' => 'You']);
          self::notifyOthers(clienttranslate('${player_name} revealed three green cards.'), ['player_name' => $this->game->getColoredPlayerName(self::getPlayerId())]);
          self::win();
        }
      }
    }
  }

  private function countRevealedGreenCards(): int
  {
    return intval(self::countCardsKeyedByColor('revealed')[$this->game::GREEN]);
  }

}