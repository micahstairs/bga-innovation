<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;

class Card445 extends Card
{

  // Space Traffic:
  //   - Draw and tuck an [11]. If you tuck directly under an [11], you lose. Otherwise, score all
  //     but your top three cards of the color of the tucked card, splay that color aslant, and if
  //     you do not have the highest score, repeat this effect.

  public function initialExecution()
  {
    do {
      $card = self::drawAndTuck(11);
      $color = $card['color'];
      $stack = self::getCardsKeyedByColor('board')[$color];
      
      if (count($stack) >= 2 && $stack[1]['faceup_age'] == 11) {
        // TODO(4E): What is supposed to happen to the player's cards. Do they get junked?
        self::lose();
        return;
      }

      for ($i = 0; $i < count($stack) - 3; $i++) {
        self::score($stack[$i]);
      }
      self::splayAslant($color);
      $hasHighestScore = true;
      $score = self::getScore();
      foreach (self::getOtherPlayerIds() as $playerId) {
        if ($score < self::getScore($playerId)) {
          $hasHighestScore = false;
        }
      }
    } while ($hasHighestScore);
  }

}