<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;

class Card345 extends Card
{

  // Lever
  //   - ECHO: Draw two [2].
  //   - You may return any number of cards from your hand. For every two cards of matching value
  //     you return, draw a card of value one higher.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::draw(2);
      self::draw(2);
    } else {
      // TODO(FIGURES): Make this array 1-based instead of 0-based.
      self::setAuxiliaryArray(array_fill(0, 11, 0)); // Track how many of each age we are returning
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getCurrentStep() === 1) {
      return [
        'can_pass'      => true,
        'n_min'         => 1,
        'n_max'         => 'all',
        'location_from' => 'hand',
        'location_to'   => 'deck',
      ];
    } else {
      $valuesToDraw = self::getAuxiliaryArray();
      $values = [];
      for ($i = 1; $i <= count($valuesToDraw); $i++) {
        if ($valuesToDraw[$i - 1] > 0) {
          $values[] = $i;
        }
      }
      return [
        'choose_value' => true,
        'age'          => $values,
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    $returnedValues = self::getAuxiliaryArray();
    $returnedValues[self::getLastSelectedAge() - 1]++;
    self::setAuxiliaryArray($returnedValues);
  }

  public function handleSpecialChoice(int $value)
  {
    $valuesLeftToDraw = self::getAuxiliaryArray();
    $valuesLeftToDraw[$value - 1]--;
    self::setAuxiliaryArray($valuesLeftToDraw);
    self::draw($value);
  }

  public function afterInteraction()
  {
    if (self::getCurrentStep() === 1) {
      $returnedValues = self::getAuxiliaryArray();
      $valuesToDraw = array_fill(0, 12, 0);
      for ($i = 0; $i < count($returnedValues); $i++) {
        $valuesToDraw[$i + 1] = $this->game->intDivision($returnedValues[$i], 2);
        if ($valuesToDraw[$i] > 0) {
          self::setMaxSteps(2);
        }
      }
      self::setAuxiliaryArray($valuesToDraw); // Repurpose array to track how many cards of each value to draw
    } else {
      // Check if there are still more cards to draw
      if (array_filter(self::getAuxiliaryArray())) {
        self::setNextStep(2);
      }
    }
  }

}