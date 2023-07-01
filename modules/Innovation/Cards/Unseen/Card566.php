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
    if (self::getEffectNumber() == 1) {
      $top9 = $this->game->getDeckTopCard(9, $this->game::BASE);
      $top10 = $this->game->getDeckTopCard(10, $this->game::BASE);

      // TODO(4E): Update this once we get clarifications on how it is supposed to work.
      if ($top9 && $top10) {
        self::setMaxSteps(2);
        self::reveal($top9);
        self::reveal($top10);
      } else if ($top9) {
        self::reveal($top9);
        self::return($top9);
      } else if ($top10) {
        self::reveal($top10);
        self::return($top10);
      }

    } else {
      if ($this->game->countCardsInLocation(self::getPlayerId(), 'board') == 0) {
        self::win();
      } else {
        foreach ($this->game->getCardsInLocation(0, 'junk') as $card) {
          if (self::isValuedCard($card)) {
            $this->game->transferCardFromTo($card, self::getPlayerId(), 'hand');
          }
        }
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() == 1) {
      return [
        'location_from' => 'revealed',
        'location_to'   => 'deck',
      ];
    } else {
      return [
        'location_from' => 'revealed',
        'location_to'   => 'hand',
      ];
    }
  }

}