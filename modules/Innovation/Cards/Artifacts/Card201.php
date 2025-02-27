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
  //   - For each top card on your board with [EFFICIENCY], draw and score a [9].
  //   - Junk all cards in the deck of value equal to the number of cards in your score pile.

  public function initialExecution()
  {
    if (self::isFirstNonDemand()) {
      $numCardsToDraw = self::countTopCardsWithEfficiency();
      for ($i = 0; $i < $numCardsToDraw; $i++) {
        self::drawAndScore(9);
      }
    } else if (self::isSecondNonDemand()) {
      self::junkBaseDeck(self::countCards(Locations::SCORE));
    }
  }

  private function countTopCardsWithEfficiency(): int
  {
    return count(self::filterByIcon(self::getTopCards(), Icons::EFFICIENCY));
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::isFourthEdition() && self::getBaseDeckCount(self::getMinValue(self::getCards(Locations::SCORE))) > 0) {
      return true;
    }
    return self::countTopCardsWithEfficiency() > 0;
  }

}