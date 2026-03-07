<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Cards\InteractionBuilder;

class Card196 extends AbstractCard
{
  // Luna 3
  // - 3rd edition:
  //   - Return all cards from your score pile. Draw and score a card of value equal to the number
  //     of cards you return.
  // - 4th edition:
  //   - Return all cards from your score pile. Draw and score a card of value equal to the number
  //     of cards you return.
  //   - Choose a value. Junk all cards in that deck.

  public function getInteractionOptions(): InteractionBuilder
  {
    if (self::isFirstNonDemand()) {
      return self::youMust()->return()->all()->fromYourScore();
    } else {
      return self::youMust()->chooseValue();
    }
  }

  public function afterInteraction()
  {
    if (self::isFirstNonDemand()) {
      self::drawAndScore(self::getNumChosen());
    }
  }

  public function handleValueChoice(int $value)
  {
    self::junkBaseDeck($value);
  }

}