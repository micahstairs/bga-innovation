<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card341_3E extends AbstractCard
{

  // Soap (3rd edition):
  //   - Choose a color. You may tuck any number of cards of that color from your hand. If you tucked
  //     at least three, you may achieve (if eligible) a card from your hand.

  public function initialExecution()
  {
    if (self::countCards(Locations::HAND) > 0) {
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->chooseColor();
    } else if (self::isSecondInteraction()) {
      return self::youMay()->tuck()->anyNumber()->withColor(self::getAuxiliaryValue())->fromYourHand();
    } else {
      return self::youMay()->achieveIfEligible()->fromYourHand();
    }
  }

  public function handleColorChoice(int $color)
  {
    self::setAuxiliaryValue($color);
  }

  public function afterInteraction()
  {
    if (self::getNumChosen() >= 3) {
      self::setMaxSteps(3);
    }
  }

}