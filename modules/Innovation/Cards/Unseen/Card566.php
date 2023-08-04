<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

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
      $card1 = self::reveal($this->game->getDeckTopCard(9, $this->game::BASE));
      $card2 = self::reveal($this->game->getDeckTopCard(10, $this->game::BASE));
      if ($card1 || $card2) {
        self::setMaxSteps(1);
      }
    } else {
      if ($this->game->countCardsInLocation(self::getPlayerId(), 'board') == 0) {
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
      'can_pass' => $this->game->countCardsInLocation(self::getPlayerId(), 'revealed') == 1,
      'location_from' => 'revealed',
      'location_to'   => 'deck',
    ];
  }

  public function afterInteraction() {
    foreach (self::getCards( 'revealed') as $card) {
      self::placeOnTopOfDeck($card);
    }
  }

}