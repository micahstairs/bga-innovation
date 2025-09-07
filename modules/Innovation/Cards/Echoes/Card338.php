<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;

class Card338 extends AbstractCard
{

  // Umbrella
  //   - ECHO: You may meld a card from your hand.
  //   - Return any number of cards from your hand. Score two cards from your hand for every card you return.

  public function getInteractionOptions(): array
  {
    if (self::isEcho()) {
      return self::youMay()->meld()->fromYourHand()->build();
    } else if (self::isFirstInteraction()) {
      return self::youMay()->return()->minCards(1)->fromYourHand()->build();
    } else {
      $numCards = self::getAuxiliaryValue() * 2;
      return self::youMust()->score()->exactly($numCards)->fromYourHand()->build();
    }
  }

  public function afterInteraction()
  {
    if (self::isNonDemand() && self::getNumChosen() > 0) {
      self::setMaxSteps(2);
      self::setAuxiliaryValue(self::getNumChosen());
    }
  }

}