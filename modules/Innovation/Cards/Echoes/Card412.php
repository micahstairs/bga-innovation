<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card412 extends AbstractCard
{

  // Tractor
  // - 3rd edition
  //   - ECHO: Draw a [7].
  //   - Draw and score a [7]. Draw a [7].
  // - 4th edition
  //   - ECHO: Draw a [7].
  //   - Draw and score a [7].
  //   - Draw a [7].
  //   - If Tractor was foreseen, draw and score seven [7].

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::draw(7);
    } else if (self::isFirstOrThirdEdition()) {
      self::drawAndScore(7);
      self::draw(7);
    } else if (self::isFirstNonDemand()) {
      self::drawAndScore(7);
    } else if (self::isSecondNonDemand()) {
      self::draw(7);
    } else if (self::wasForeseen()) {
      for ($i = 0; $i < 7; $i++) {
        self::drawAndScore(7);
      }
    }
  }

}