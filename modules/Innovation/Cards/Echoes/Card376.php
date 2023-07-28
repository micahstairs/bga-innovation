<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card376 extends Card
{

  // Thermometer
  // - 3rd edition:
  //  - ECHO: Meld your bottom green card. Maintain its splay.
  //  - Draw and meld a card of value one higher than the value of your top yellow card. If the
  //    melded card is yellow, repeat this dogma effect.
  // - 4th edition:
  //  - ECHO: Meld your bottom green card.
  //  - Draw and meld a card of value one higher than the value of your top yellow card. If the
  //    melded card is yellow, or if Thermometer was foreseen and the melded card is red or purple,
  //    repeat this effect.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::meld(self::getBottomCardOfColor($this->game::GREEN));
    } else {
      do {
        $topCard = self::getTopCardOfColor($this->game::YELLOW);
        $value = $topCard ? $topCard['faceup_age'] + 1 : 0;
        $meldedCard = self::drawAndMeld($value);
        $repeat = $meldedCard['color'] == $this->game::YELLOW;
        if (!$repeat && self::isFourthEdition() && self::wasForeseen()) {
          $repeat = $meldedCard['color'] == $this->game::RED || $meldedCard['color'] == $this->game::PURPLE;
        }
      } while ($repeat);
    }
  }

}