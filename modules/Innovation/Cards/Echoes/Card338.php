<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card338 extends AbstractCard
{

  // Umbrella
  //   - ECHO: You may meld a card from your hand.
  //   - Return any number of cards from your hand. Score two cards from your hand for every card you return.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isEcho()) {
      return self::youMay()->meld()->fromYourHand();
    } else if (self::isFirstInteraction()) {
      return self::youMay()->return()->minCards(1)->fromYourHand();
    } else {
      $numCards = self::getAuxiliaryValue() * 2;
      return self::youMust()->score()->exactly($numCards)->fromYourHand();
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