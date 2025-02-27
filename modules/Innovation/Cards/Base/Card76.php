<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card76 extends AbstractCard
{

  // Rocketry:
  // - 3rd edition:
  //   - Return a card in any opponent's score pile for every two [EFFICIENCY] on your board.
  // - 4th edition:
  //   - Return a card in any opponent's score pile for every color on your board with [EFFICIENCY].

  public function getInteractionOptions(): array
  {
    if (self::isFirstOrThirdEdition()) {
      $numCards = $this->game->intDivision(self::getStandardIconCount(Icons::EFFICIENCY), 2);
    } else {
      $numCards = self::countColorsWithIcon(Icons::EFFICIENCY);
    }
    return self::youMust()->return()->exactly($numCards)->fromOpponentsScore()->build();
  }

  public function nonDemandsMightBeEffective(): bool
  {
    if (self::isFirstOrThirdEdition() && self::getStandardIconCount(Icons::EFFICIENCY) < 2) {
      return false;
    }
    if (self::isFourthEdition() && self::countColorsWithIcon(Icons::EFFICIENCY) == 0) {
      return false;
    }
    foreach (self::getOpponentIds() as $opponentId) {
      if (self::hasCards(Locations::SCORE, $opponentId)) {
        return true;
      }
    }
    return false;
  }

}