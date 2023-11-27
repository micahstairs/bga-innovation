<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card442 extends AbstractCard
{

  // Astrogeology:
  //   - Draw and reveal an [11]. Splay its color on your board aslant. If you do, transfer all but
  //     your top four cards of that color into your hand.
  //   - If you have at least eight cards in your hand, you win.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $revealedCard = self::drawAndReveal(11);
      $color = $revealedCard['color'];
      if (self::splayAslant($color)) {
        $stack = self::getStack($color);
        for ($i = 0; $i < count($stack) - 4; $i++) {
          self::transferToHand($stack[$i]);
        }
      }
      self::transferToHand($revealedCard);
    } else if (self::isSecondNonDemand()) {
      if (self::countCards(Locations::HAND) >= 8) {
        self::notifyPlayer(clienttranslate('${You} have eight or more cards in your hand.'));
        self::notifyOthers(clienttranslate('${player_name} has eight or more cards in his hand.'));
        self::win();
      }
    }
  }

}