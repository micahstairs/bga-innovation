<?php

namespace Innovation\Cards\Echoes;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;
use Innovation\Enums\CardTypes;

class Card332 extends AbstractCard
{

  // Ruler
  // - 3rd edition:
  //   - ECHO: Draw a [2].
  //   - No effect.
  // - 4th edition:
  //   - ECHO: Draw a [2].
  //   - Draw two Echoes [1]. Foreshadow one of them and return the other.

  public function initialExecution()
  {
    if (self::isEcho()) {
      self::draw(2);
    } else {
      $card1 = self::drawType(1, CardTypes::ECHOES);
      $card2 = self::drawType(1, CardTypes::ECHOES);
      self::setAuxiliaryArray([self::getId($card1), self::getId($card2)]);
      self::setMaxSteps(2);
    }
  }

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->foreshadow()->onlyCardsInAuxiliaryArray()->fromYourHand();
    } else {
      return self::youMust()->return()->onlyCardsInAuxiliaryArray()->fromYourHand();
    }
  }

  public function handleCardChoice(array $card)
  {
    if (self::isFirstInteraction()) {
      self::removeFromAuxiliaryArray(self::getId($card));
    }
  }

}