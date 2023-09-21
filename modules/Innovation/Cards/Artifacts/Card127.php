<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;

class Card127 extends AbstractCard
{

  // Chronicle of Zuo
  // - 3rd edition:
  //   - If you have the least [AUTHORITY], draw a [2]. If you have the least [PROSPERITY], draw
  //     a [3]. If you have the least [CONCEPT], draw a [4].
  // - 4th edition:
  //   - If you have the least [AUTHORITY] or the least [PROSPERITY], draw a [3]. 
  //   - If you have the least [CONCEPT], draw a [4].

  public function initialExecution()
  {
    $iconCountsByPlayer = self::getStandardIconCountsOfAllPlayers();
    if (self::isFirstOrThirdEdition()) {
      if (self::hasLeastIcons($iconCountsByPlayer, Icons::AUTHORITY)) {
        self::draw(2);
      }
      if (self::hasLeastIcons($iconCountsByPlayer, Icons::PROSPERITY)) {
        self::draw(3);
      }
      if (self::hasLeastIcons($iconCountsByPlayer, Icons::CONCEPT)) {
        self::draw(4);
      }
    } else if (self::isFourthEdition() && self::isFirstNonDemand()) {
      if (self::hasLeastIcons($iconCountsByPlayer, Icons::AUTHORITY) || self::hasLeastIcons($iconCountsByPlayer, Icons::PROSPERITY)) {
        self::draw(3);
      }
    } else if (self::isFourthEdition() && self::isSecondNonDemand()) {
      if (self::hasLeastIcons($iconCountsByPlayer, Icons::CONCEPT)) {
        self::draw(4);
      }
    }
  }

  private function hasLeastIcons(array $iconCountsByPlayer, $icon): bool
  {
    $minIcons = null;
    foreach ($iconCountsByPlayer as $player => $iconCounts) {
      if ($minIcons === null || $iconCounts[$icon] < $minIcons) {
        $minIcons = $iconCounts[$icon];
      }
    }
    return $iconCountsByPlayer[self::getPlayerId()][$icon] === $minIcons;
  }

}