<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

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
      $card1 = self::drawFromSet(1, $this->game::ECHOES);
      $card2 = self::drawFromSet(1, $this->game::ECHOES);
      self::setAuxiliaryArray([$card1['id'], $card2['id']]);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      return [
        'location_from'                   => 'hand',
        'location_to'                     => 'forecast',
        'card_ids_are_in_auxiliary_array' => true,
      ];
    } else {
      return [
        'location_from'                   => 'hand',
        'location_to'                     => 'deck',
        'card_ids_are_in_auxiliary_array' => true,
      ];
    }
  }

  public function handleCardChoice(array $card) {
    if (self::getCurrentStep() === 1) {
      // Remove forecasted card from auxiliary array
      self::setAuxiliaryArray(array_diff(self::getAuxiliaryArray(), [$card['id']]));
    }
  }

}