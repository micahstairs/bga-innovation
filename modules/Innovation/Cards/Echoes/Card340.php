<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card340 extends Card
{

  // Noodles
  // - 3rd edition:
  //   - If you have more [1]s in your hand than every other player, draw and score a [2].
  //   - Draw and reveal a [1]. If it is yellow, score all [1]s from your hand.
  // - 4th edition:
  //   - If you have more [1]s in your hand than every other opponent, draw and score a [2].
  //   - Draw and reveal a [1]. If it is yellow, score all [1] from your hand.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      $numCards = $this->game->countCardsInLocationKeyedByAge(self::getPlayerId(), 'hand')[1];
      if ($numCards > 0) {
        $willScore = true;
        $playerIds = self::isFirstOrThirdEdition() ? self::getOtherPlayerIds(self::getPlayerId()) : self::getOpponentIds(self::getPlayerId());
        foreach ($playerIds as $otherPlayerId) {
          if ($numCards <= $this->game->countCardsInLocationKeyedByAge($otherPlayerId, 'hand')[1]) {
            $willScore = false;
            break;
          }
        }
        if ($willScore) {
          self::drawAndScore(2);
        }
      }
    } else {
      $card = self::drawAndReveal(1);
      $this->notifications->notifyCardColor($card['color']);
      self::putInHand($card);
      if ($card['color'] == $this->game::YELLOW) {
        foreach (self::getCardsKeyedByValue('hand')[1] as $card) {
          self::score($card);
        }
      }
    }
  }

}