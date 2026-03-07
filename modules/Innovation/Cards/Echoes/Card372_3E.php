<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card372_3E extends AbstractCard
{

  // Pencil (3rd edition):
  //   - ECHO: Draw a [5].
  //   - You may return up to three cards from your hand. If you do, draw that many cards of value
  //     one higher than the highest card you returned. Foreshadow one of them, and return the rest
  //     of the drawn cards.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::draw(5);
    } else {
      self::setAuxiliaryValue(0); // Track the value of the highest returned card
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMay()->return()->minCards(1)->maxCards(3)->fromYourHand();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->foreshadow()->onlyCardsInAuxiliaryArray()->fromYourHand();
    } else {
      return self::youMust()->return()->all()->onlyCardsInAuxiliaryArray()->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::setAuxiliaryValue(max(self::getAuxiliaryValue(), self::getValue($card)));
    } else if (self::isSecondInteraction()) {
      self::removeFromAuxiliaryArray(self::getId($card));
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstInteraction() && self::getNumChosen() > 0) {
      $valueToDraw = self::getAuxiliaryValue() + 1;
      $cardIds = [];
      for ($i = 1; $i <= self::getNumChosen(); $i++) {
        $card = self::draw($valueToDraw);
        $cardIds[] = self::getId($card);
      }
      self::setAuxiliaryArray($cardIds); // Track cards to foreshadow/return
      self::setMaxSteps(3);
    }
  }

}