<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\Card;
use Innovation\Enums\Locations;

class Card166 extends Card
{
  // Puffing Billy
  // - 3rd edition:
  //   - Return a card from your hand. Draw a card of value equal to the highest number of symbols
  //     of the same type visible in that color on your board. Splay right that color.
  // - 4th edition:
  //   - Tuck a card from your hand. Splay right its color on your board. Draw a card of value
  //     equal to the highest number of icons of the same type visible in that color on your board.

  public function initialExecution()
  {
    self::setMaxSteps(1);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return [
        'location_from' => Locations::HAND,
        'location_to'   => Locations::REVEALED_THEN_DECK,
      ];
    } else {
      return [
        'location_from' => Locations::HAND,
        'tuck_keyword'  => true,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFourthEdition()) {
      self::splayRight($card['color']);
    }

    $countsByIcon = self::getAllIconCountsInStack($card['color']);
    $maxCount = max(array_values($countsByIcon));
    self::draw($maxCount);

    if (self::isFirstOrThirdEdition()) {
      self::splayRight($card['color']);
    }
  }
}