<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Enums\CardTypes;

class Card332 extends Card
{

  // Ruler
  // - 3rd edition:
  //   - ECHO: Draw a [2].
  //   - No effect.
  // - 4th edition:
  //   - ECHO: Draw a [2].
  //   - Draw two Echoes [1]. Foreshadow one of them and return the other.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::draw(2);
    } else {
      $card1 = self::drawType(1, CardTypes::ECHOES);
      $card2 = self::drawType(1, CardTypes::ECHOES);
      self::setAuxiliaryArray([$card1['id'], $card2['id']]);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    $keyword = self::isFirstInteraction() ? 'foreshadow_keyword' : 'return_keyword';
    return [
      'location_from'                   => 'hand',
      $keyword                          => true,
      'card_ids_are_in_auxiliary_array' => true,
    ];
  }

  public function handleCardChoice(array $card) {
    if (self::isFirstInteraction()) {
      self::removeFromAuxiliaryArray($card['id']);
    }
  }

}