<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card582 extends AbstractCard
{

  // Whatchamacallit:
  //   - For each value, in ascending order, if that value is not a value of a top card on your board or a card in your score pile, draw and score a card of that value.

  public function initialExecution()
  {
    $values = [];
    foreach (self::getTopCards() as $card) {
      if (!in_array(self::getFaceupValue($card), $values)) {
        $values[] = self::getFaceupValue($card);
      }
    }
    foreach (self::getCards(Locations::SCORE) as $card) {
      if (!in_array(self::getValue($card), $values)) {
        $values[] = self::getValue($card);
      }
    }
    // For each value, in ascending order, if that value is not a value of a top card on your board or a card in your score pile, draw and score a card of that value.
    for ($i = 1; $i <= 11; $i++) {
      if (!in_array($i, $values)) {
        self::drawAndScore($i);
      }
    }
  }

}