<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card499 extends AbstractCard
{

  // Cipher
  //   - Return all cards from your hand. If you return at least two, draw a card of value one higher than
  //     the highest value of card you return.
  //   - Draw a [2]. You may splay your blue cards left.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      if (self::hasCards(Locations::HAND)) {
        self::setAuxiliaryValue(0);
        self::setMaxSteps(1);
      }
    } else if (self::isSecondNonDemand()) {
      self::draw(2);
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->return()->all()->fromYourHand();
    } else {
      return self::youMay()->splayLeft(Colors::BLUE);
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstNonDemand()) {
      // Keep track of highest card returned
      self::setAuxiliaryValue(max(self::getValue($card), self::getAuxiliaryValue()));
    }
    self::transferToHand($card);
    self::setMaxSteps(2);
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand() && self::getNumChosen() >= 2) {
      self::draw(self::getAuxiliaryValue() + 1);
    }
  }

}