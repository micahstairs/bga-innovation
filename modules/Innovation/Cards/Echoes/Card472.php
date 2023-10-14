<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card472 extends AbstractCard
{

  // Drone
  //   - Draw and reveal a [10]. If you have fewer than six cards of that color on your board,
  //     splay that color aslant on your board. Otherwise, return the bottom five cards of that
  //     color from all boards. If you do, repeat this effect.

  public function initialExecution()
  {
    do {
      $repeat = false;
      $card = self::transferToHand(self::drawAndReveal(10));
      $color = $card['color'];
      if (self::countCardsKeyedByColor('board')[$color] < 6) {
        self::splayAslant($color);
      } else {
        foreach (self::getPlayerIds() as $playerId) {
          foreach (self::getStack($color, $playerId) as $card) {
            if ($card['position'] < 5) {
              self::return($card);
              $repeat = true;
            }
          }
        }
      }
    } while ($repeat);
  }

}