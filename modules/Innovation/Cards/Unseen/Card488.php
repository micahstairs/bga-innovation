<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;

class Card488 extends AbstractCard
{

  // Silk:
  //   - Meld a card from your hand.
  //   - You may score a card from your hand of each color on your board.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(1);
      self::setAuxiliaryValue(1); // Track whether the player is allowed to pass the second interaction
      self::setAuxiliaryArray(self::getColorsOnBoard()); // Track which colors still may be scored from hand
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->meld()->fromYourHand()->build();
    } else if (self::getAuxiliaryValue() === 1) {
      return self::youMay()->score()->withColor(self::getAuxiliaryArray())->fromYourHand()->revealingIfUnable()->build();
    } else {
      return self::youMust()->score()->withColor(self::getAuxiliaryArray())->fromYourHand()->revealingIfUnable()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isSecondNonDemand() && self::getNumChosen() > 0) {
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
      if (!in_array(self::getColor($card), $colors)) {
        $colors[] = self::getColor($card);
      }
    }
    return $colors;
  }
}