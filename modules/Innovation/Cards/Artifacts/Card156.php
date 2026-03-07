<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card156 extends AbstractCard
{
  // Principia
  // - 3rd edition:
  //   - Return all non-blue top cards from your board. For each card returned, draw and meld a
  //     card of value one higher than the value of the returned card, in ascending order.
  // - 4th edition:
  //   - Return your top card of each non-blue color. For each card you return, draw and meld a
  //     card of value one higher than the value of the returned card, in ascending order.

  public function getInteractionOptions(): InteractionBuilder
  {
    self::setAuxiliaryArray([]);
    return self::youMust()->return()->all()->non(Colors::BLUE)->fromYourBoard();
  }

  public function handleCardChoice(array $card)
  {
    self::addToAuxiliaryArray(self::getFaceupValue($card));
  }

  public function afterInteraction()
  {
    $values = self::getAuxiliaryArray();
    sort($values);
    foreach ($values as $value) {
      self::drawAndMeld($value + 1);
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::filterByColor(self::getCards(Locations::BOARD), Colors::NON_BLUE)) > 0;
  }
}
