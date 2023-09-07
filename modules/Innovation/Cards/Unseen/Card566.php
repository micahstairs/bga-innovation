<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;
use Innovation\Enums\CardTypes;

class Card566 extends Card
{

  // Fermi Paradox:
  //   - Reveal the top card of the [9] deck and the [10] deck. Return the top card of the [9] deck
  //     or the [10] deck.
  //   - If you have no cards on your board, you win. Otherwise, transfer all valued junked cards
  //     to your hand.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      $card1 = $this->game->getDeckTopCard(9, CardTypes::BASE);
      if ($card1) {
        $this->game->transferCardFromTo($card1, self::getPlayerId(), 'revealed', ['draw_keyword' => false]);
        self::setMaxSteps(1);
      }
      $card2 = $this->game->getDeckTopCard(10, CardTypes::BASE);
      if ($card2) {
        $this->game->transferCardFromTo($card2, self::getPlayerId(), 'revealed', ['draw_keyword' => false]);
        self::setMaxSteps(1);
      }
    } else {
      if (self::countCards('board') === 0) {
        self::win();
      } else {
        foreach (self::getCards('junk') as $card) {
          if (self::isValuedCard($card)) {
            self::transferToHand($card);
          }
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    return [
      'can_pass'      => self::countCards('revealed') === 1,
      'location_from' => 'revealed',
      'location_to'   => 'deck',
    ];
  }

  public function afterInteraction()
  {
    foreach (self::getCards('revealed') as $card) {
      self::placeOnTopOfDeck($card);
    }
  }

}