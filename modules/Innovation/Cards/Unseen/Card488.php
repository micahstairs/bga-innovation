<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\Card;

class Card488 extends Card
{

  // Silk:
  //   - Meld a card from your hand.
  //   - You may score a card from your hand of each color on your board.

  public function initialExecution()
  {
    if (self::getEffectNumber() === 1) {
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(1);
      self::setAuxiliaryValue(1); // Track whether the player is allowed to pass the second interaction
      self::setAuxiliaryArray(self::getColorsOnBoard()); // Track which colors still may be scored from hand
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::getEffectNumber() === 1) {
      return [
        'location_from' => 'hand',
        'meld_keyword'  => true,
      ];
    } else {
      return [
        'can_pass'      => self::getAuxiliaryValue() === 1,
        'location_from' => 'hand',
        'color'         => self::getAuxiliaryArray(),
        'score_keyword' => true,
      ];
    }
  }

  public function afterInteraction()
  {
    if (self::getEffectNumber() === 2 && self::getNumChosen() > 0) {
      // Do not allow the player to pass when the interaction is repeated
      self::setAuxiliaryValue(0);
      // Do not allow the same color to be chosen again
      self::removeFromAuxiliaryArray(self::getLastSelectedColor());
      self::setNextStep(1);
    }
  }

  private function getColorsOnBoard(): array
  {
    $colors = [];
    foreach (self::getTopCards() as $card) {
      if (!in_array($card['color'], $colors)) {
        $colors[] = $card['color'];
      }
    }
    return $colors;
  }
}