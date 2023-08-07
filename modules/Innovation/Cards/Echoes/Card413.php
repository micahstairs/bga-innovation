<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\Card;
use Innovation\Utils\Arrays;

class Card413 extends Card
{

  // Crossword
  // - 3rd edition
  //   - For each visible bonus on your board, draw a card of that value.
  // - 4th edition
  //   - For each even bonus on your board, draw a card of that value. If Crossword was foreseen,
  //     transfer the drawn cards to the available achievements.
  //   - For each odd bonus on your board, return the lowest card from your hand.

  // TODO(#1262): Implement 4th edition of Crossword.

  public function initialExecution()
  {
    if (self::isFirstOrThirdEdition()) {
      $bonuses = self::getBonuses();
      if (count($bonuses) > 0) {
        self::setAuxiliaryArray($bonuses);
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      return [
        'choose_value' => true,
        'age'          => self::getAuxiliaryArray(),
      ];
    }
  }

  public function handleSpecialChoice($value)
  {
    self::draw($value);
    $remainingValues = Arrays::removeElement(self::getAuxiliaryArray(), $value);
    if (count($remainingValues) > 0) {
      self::setAuxiliaryArray($remainingValues);
      self::setNextStep(1);
    }
  }

}