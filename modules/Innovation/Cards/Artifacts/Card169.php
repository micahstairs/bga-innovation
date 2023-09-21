<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card169 extends AbstractCard
{
  // The Wealth of Nations
  // - 3rd edition:
  //   - Draw and score a [1]. Add up the values of all cards in your score pile, divide by five,
  //     and round up. Draw and score a card of value equal to the result.
  // - 4th edition:
  //   - Draw and score a [1]. Add up the values of all cards in your score pile, divide by five,
  //     and round up. Draw and score a card of value equal to the result. Junk all cards in the
  //     deck of that value.

  public function initialExecution()
  {
    self::drawAndScore(1);
    $sum = array_sum(self::getValues(self::getCards(Locations::SCORE)));
    $value = ceil($sum / 5);
    self::drawAndScore($value);
    if (self::isFourthEdition()) {
      self::junkBaseDeck($value);
    }
  }

}