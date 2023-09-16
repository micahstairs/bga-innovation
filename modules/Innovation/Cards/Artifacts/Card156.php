<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card156 extends Card
{
  // Principia
  // - 3rd edition:
  //   - Return all non-blue top cards from your board. For each card returned, draw and meld a
  //     card of value one higher than the value of the returned card, in ascending order.
  // - 4th edition:
  //   - Return your top card of each non-blue color. For each card you return, draw and meld a
  //     card of value one higher than the value of the returned card, in ascending order.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    self::setAuxiliaryArray([]);
    return [
      'n'              => 'all',
      'location_from'  => Locations::BOARD,
      'return_keyword' => true,
      'color'          => Colors::NON_BLUE,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::addToAuxiliaryArray($card['faceup_age']);
  }

  public function afterInteraction()
  {
    $values = self::getAuxiliaryArray();
    sort($values);
    foreach ($values as $value) {
      self::drawAndMeld($value + 1);
    }
  }
}