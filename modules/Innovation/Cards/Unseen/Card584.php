<?php

namespace Innovation\Cards\Unseen;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Colors;
use Innovation\Enums\Locations;

class Card584 extends AbstractCard
{

  // Order of the Occult Hand:
  //   - If you have a [3] in your score pile, you lose.
  //   - If you have a [7] in your hand, you win.
  //   - Meld two cards from your hand. Score four cards from your hand. Splay your blue cards up.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      if (self::countCardsKeyedByValue(Locations::SCORE)[3] > 0) {
        self::lose();
      }
    } else if (self::isSecondNonDemand()) {
      if (self::countCardsKeyedByValue(Locations::HAND)[7] > 0) {
        self::win();
      }
    } else if (self::isThirdNonDemand()) {
      self::setMaxSteps(3);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isFirstInteraction()) {
      return self::youMust()->meld()->exactly(2)->fromYourHand()->build();
    } else if (self::isSecondInteraction()) {
      return self::youMust()->score()->exactly(4)->fromYourHand()->build();
    } else {
      return self::youMust()->splayUp(Colors::BLUE)->build();
    }
  }

}