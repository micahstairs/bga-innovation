<?php

namespace Innovation\Cards\Artifacts;

use Innovation\Cards\AbstractCard;

class Card137 extends AbstractCard
{

  // Excalibur
  //   - I COMPEL you to transfer a top card of higher value than my top card of the same color
  //     from your board to my board!

  public function getInteractionOptions(): array
  {
    return self::youMust()->fromYourBoard()->withColor(self::getEligibleColors())->toMine()->build();
  }

  private function getEligibleColors(): array
  {
    $colors = [];
    foreach (self::getTopCards() as $playerCard) {
      $launcherCard = self::getTopCardOfColor(self::getColor($playerCard), self::getLauncherId());
      if ($launcherCard === null || self::getValue($playerCard) > self::getValue($launcherCard)) {
        $colors[] = self::getColor($playerCard);
      }
    }
    return $colors;
  }

  public function compelMightBeEffective(): bool
  {
    return count($this->getEligibleColors()) > 0;
  }

}