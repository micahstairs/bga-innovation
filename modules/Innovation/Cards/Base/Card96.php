<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;

class Card96 extends AbstractCard
{
  // Software:
  // - 3rd edition:
  //   - Draw and score a [10].
  //   - Draw and meld two [10], then execute each of the second card's non dogma effects. Do not share them.
  // - 4th edition:
  //   - Draw and score a [10].
  //   - Draw and meld two [9], then self-execute the second card.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::drawAndScore(10);
    } else if (self::isSecondNonDemand()) {
      $value = self::isFourthEdition() ? 9 : 10;
      self::drawAndMeld($value);
      $secondCard = self::drawAndMeld($value);
      self::selfExecute($secondCard);
    }
  }

}