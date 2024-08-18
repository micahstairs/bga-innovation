<?php

namespace Innovation\Cards\Base;

use Innovation\Cards\AbstractCard;
use Innovation\Enums\Locations;
use Innovation\Enums\ValueSelectors;

class Card3 extends AbstractCard
{

  // Archery:
  // - 3rd edition:
  //   - I DEMAND you draw a [1], then transfer the highest card in your hand to my hand!
  // - 4th edition:
  //   - I DEMAND you draw a [1], then transfer the highest card in your hand to my hand!
  //   - Junk an available achievement of value [1] or [2].

  public function initialExecution()
  {
    if (self::isDemand()) {
      self::draw(1);
      self::setMaxSteps(1);
    } else {
      self::setMaxSteps(1);
    }
  }

  public function getInteractionOptions(): array
  {
    if (self::isDemand()) {
      return [
        'age'      => ValueSelectors::HIGHEST,
        'location' => Locations::HAND,
        'owner_to' => self::getLauncherId(),
      ];
    } else {
      return [
        'junk_keyword'  => true,
        'location_from' => Locations::AVAILABLE_ACHIEVEMENTS,
        'age_min'       => 1,
        'age_max'       => 2,
      ];
    }
  }

  public function nonDemandsMightBeEffective(): bool
  {
    return count(self::filterByValue(self::getAvailableStandardAchievements(), [1, 2])) > 0;
  }

}