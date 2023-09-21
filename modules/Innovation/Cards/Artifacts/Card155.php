<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;

class Card155 extends AbstractCard
{
  // Boerhavve Silver Microscope (3rd edition)
  //   - Return the lowest card in your hand and the lowest top card on your board. Draw and score
  //     a card of value equal to the sum of the values of the cards returned.
  // Boerhaave Microscope (4th edition)
  //   - Return the lowest card in your hand and the lowest top card on your board. Draw and score
  //     a card of value equal to the sum of the values of the cards you return.

  public function initialExecution()
  {
    self::setMaxSteps(2);
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(0); // Track value of first returned card
      return [
        'location_from'  => Locations::HAND,
        'return_keyword' => true,
        'age'            => self::getMinValueInLocation(Locations::HAND),
      ];
    } else {
      return [
        'location_from'  => Locations::BOARD,
        'return_keyword' => true,
        'age'            => self::getMinValue(self::getTopCards()),
      ];
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue($card['age']);
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction()) {
      $firstValue = self::getAuxiliaryValue();
      $secondValue = self::getNumChosen() === 1 ? self::getFaceupAgeLastSelected() : 0;
      self::drawAndScore($firstValue + $secondValue);
    }
  }
}