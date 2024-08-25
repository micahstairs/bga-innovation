<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Icons;
use Innovation\Enums\Locations;

class Card54 extends AbstractCard
{
  // Societies:
  // - 3rd edition:
  //   - I DEMAND you transfer a top card with a [CONCEPT] higher than my top card of the same
  //     color from your board to my board! If you do, draw an [5]!
  // - 4th edition:
  //   - I DEMAND you transfer a top card with [CONCEPT] higher than my top card of the same
  //     color from your board to my board! If you do, draw an [5]!

  public function getInteractionOptions(): array
  {
    return [
      'location'   => Locations::BOARD,
      'owner_from' => self::getPlayerId(),
      'owner_to'   => self::getLauncherId(),
      'color'      => self::getEligibleColors(),
      'with_icon'  => Icons::CONCEPT,
    ];
  }

  public function handleCardChoice(array $card)
  {
    self::draw(5);
  }

  private function getEligibleColors(): array
  {
    $colors = [];
    foreach (self::filterByIcon(self::getTopCards(), Icons::CONCEPT) as $playerCard) {
      $launcherCard = self::getTopCardOfColor(self::getColor($playerCard), self::getLauncherId());
      if (self::getValue($playerCard) > self::getValue($launcherCard)) {
        $colors[] = self::getColor($playerCard);
      }
    }
    return $colors;
  }

  public function demandMightBeEffective(): bool
  {
    return count(self::getEligibleColors()) > 0;
  }

}