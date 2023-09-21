<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;

class Card376 extends AbstractCard
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
      self::meld(self::getBottomCardOfColor(Colors::GREEN));
    } else {
      do {
        $topCard = self::getTopCardOfColor(Colors::YELLOW);
        $value = $topCard ? $topCard['faceup_age'] + 1 : 0;
        $meldedCard = self::drawAndMeld($value);
        $repeat = self::isYellow($meldedCard);
        if (!$repeat && self::wasForeseen()) {
          $repeat = self::isRed($meldedCard) || self::isPurple($meldedCard);
        }
      } while ($repeat);
    }
  }

}