<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;
use Innovation\Enums\Icons;

class Card201 extends AbstractCard
{
  // Rock Around the Clock
  // - 3rd edition:
  //   - For each top card on your board with a [EFFICIENCY], draw and score a [9].
  // - 4th edition:
  //   - For each top card on your board with a [EFFICIENCY], draw and score a [9].
  //   - Junk all cards in the deck of value equal to the number of cards in your score pile.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      foreach (self::getTopCards() as $card) {
        if (self::hasIcon($card, Icons::EFFICIENCY)) {
          self::drawAndScore(9);
        }
      }
    } else if (self::isSecondNonDemand()) {
      self::junkBaseDeck(self::countCards(Locations::SCORE));
    }
  }

}