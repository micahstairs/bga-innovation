<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card503 extends AbstractCard
{

  // Propaganda:
  //   - I DEMAND you meld a card of the color of my choice from your hand! If you do, transfer
  //     the card beneath it to my board!
  //   - Meld a card from your hand.

  public function initialExecution()
  {
    self::setMaxSteps(self::isDemand() ? 2 : 1);
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      if (self::isFirstInteraction()) {
        return self::youMust()->chooseColor()->ofMyChoice();
      } else {
        return self::youMust()->meld()->withColor(self::getAuxiliaryValue())->fromYourHand()->revealingIfUnable();
      }
    } else {
      return self::youMust()->meld()->fromYourHand();
    }
  }

  public function handleColorChoice(int $color)
  {
    self::notifyColorChoice($color);
    self::setAuxiliaryValue($color); // Track color to meld
  }

  public function afterInteraction()
  {
    if (self::isDemand() && self::isSecondInteraction()) {
      if (self::getNumChosen() > 0) {
        $stack = self::getStack(self::getAuxiliaryValue());
        if (count($stack) >= 2) {
          self::transferToBoard($stack[count($stack) - 2], self::getLauncherId());
        }
      }
    }
  }

}