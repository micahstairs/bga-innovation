<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card190 extends AbstractCard
{
  // Meiji-Mura Stamp Vending Machine
  // - 3rd edition:
  //   - Return a card from your hand. Draw and score three cards of the returned card's value.
  // - 4th edition:
  //   - Return a card from your hand. Draw and score three cards of the returned card's value. If
  //     you don't, junk all cards in the deck of value equal to the highest scored card.

  public function getInteractionOptions(): InteractionBuilder
  {
    return self::youMust()->return()->fromYourHand();
  }

  public function afterInteraction()
  {
    $valueToDraw = 0;
    if (self::getNumChosen() > 0) {
      $valueToDraw = self::getLastSelectedAge();
    }
    $value1 = self::getValue(self::drawAndScore($valueToDraw));
    $value2 = self::getValue(self::drawAndScore($valueToDraw));
    $value3 = self::getValue(self::drawAndScore($valueToDraw));
    if (self::isFourthEdition() && ($value1 != $valueToDraw || $value2 != $valueToDraw || $value3 != $valueToDraw)) {
      self::junkBaseDeck(max($value1, $value2, $value3));
    }
  }

}