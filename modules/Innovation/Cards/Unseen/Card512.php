<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card512 extends AbstractCard
{

  // Secret Police:
  //   - I DEMAND you tuck a card in your hand, then return your top card of its color! If you do,
  //     repeat this effect! Otherwise, draw a [3]!
  //   - You may tuck any number of cards of any one color from your hand.

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::setMaxSteps(1);
    } else {
      // There is no need to pick a color if the hand is empty
      if (self::countCards('hand') > 0) {
        self::setMaxSteps(1);
      }
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isDemand()) {
      return self::youMust()->tuck()->fromYourHand();
    } else if (self::isFirstInteraction()) {
      return self::youMay()->chooseColor();
    } else {
      return self::youMay()->tuck()->anyNumber()->withColor(self::getAuxiliaryValue())->fromYourHand();
    }
  }

  public function handleColorChoice(int $color)
  {
    self::setMaxSteps(2);
    self::setAuxiliaryValue($color);
  }

  public function afterInteraction()
  {
    if (self::isDemand()) {
      if (self::getNumChosen() > 0 && self::return(self::getTopCardOfColor(self::getLastSelectedColor()))) {
        self::setNextStep(1);
      } else {
        self::draw(3);
      }
    }
  }

}