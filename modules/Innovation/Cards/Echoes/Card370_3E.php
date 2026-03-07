<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Locations;

class Card370_3E extends AbstractCard
{

  // Globe (3rd edition):
  //   - You may return up to three cards from hand of the same color. If you return one, splay
  //     any color left; two, right; three, up. If you returned at least one card, draw and
  //     foreshadow a [6].

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMay()->chooseColor(self::getUniqueColorsInLocation(Locations::HAND));
    } else if (self::isSecondInteraction()) {
      return self::youMay()
        ->revealAndReturn()
        ->minCards(1)
        ->maxCards(3)
        ->fromYourHand()
        ->withColor([self::getAuxiliaryValue()]);
    } else {
      return self::youMust()->splayInDirection(self::getAuxiliaryValue());
    }
  }

  public function handleColorChoice(int $color)
  {
    self::setAuxiliaryValue($color); // Track color being returned
    self::setMaxSteps(2);
  }

  public function afterInteraction()
  {
    if (self::isSecondInteraction() && self::getNumChosen() > 0) {
      self::setAuxiliaryValue(self::getNumChosen()); // Repurpose auxiliary value to store the number of cards returned
      self::setMaxSteps(3);
    } else if (self::isThirdInteraction() && self::getAuxiliaryValue() > 0) {
      self::drawAndForeshadow(6);
    }
  }

  public function handleAbortedInteraction()
  {
    if (self::isThirdInteraction() && self::getAuxiliaryValue() > 0) {
      self::drawAndForeshadow(6);
    }
  }

}