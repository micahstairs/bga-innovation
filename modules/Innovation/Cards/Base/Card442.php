<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card442 extends Card
{

  // Astrogeology:
  //   - Draw and reveal an [11]. Splay its color on your board aslant. If you do, transfer all but
  //     your top two cards of that color into your hand.
  //   - If you have eleven cards in your hand, you win.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $revealedCard = self::drawAndReveal(11);
      $color = $revealedCard['color'];
      if (self::splayAslant($color)) {
        $stack = self::getStack($color);
        for ($i = 0; $i < count($stack) - 2; $i++) {
          self::transferToHand($stack[$i]);
        }
      }
      self::transferToHand($revealedCard);
    } else {
      if (self::countCards(Locations::HAND) >= 11) {
        self::notifyPlayer(clienttranslate('${You} have 11 or more cards in your hand.'));
        self::notifyOthers(clienttranslate('${player_name} has 11 or more cards in his hand.'));
        self::win();
      }
    }
  }

}