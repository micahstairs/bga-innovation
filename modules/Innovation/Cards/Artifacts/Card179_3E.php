<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card179_3E extends AbstractCard
{
  // International Prototype Metre Bar (3rd edition):
  //   - Choose a value. Draw and meld a card of that value. Splay up the color of the melded card.
  //     If the number of cards of that color visible on your board is exactly equal to the card's
  //     value, you win. Otherwise, return the melded card.

  public function getInteractionOptions(): array
  {
    return self::youMust()->chooseValue()->build();
  }

  public function handleValueChoice(int $value)
  {
    self::notifyValueChoice($value);
    $card = self::drawAndMeld($value);
    self::splayUp(self::getColor($card));
    if (self::getFaceupValue($card) == self::countVisibleCardsInStack(self::getColor($card))) {
      self::win();
    } else {
      self::return($card);
    }
  }

}